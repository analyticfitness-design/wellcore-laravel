<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ExerciseMetadata;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * kb:import-exercise-catalog — descarga los 265 .gif paths del repo público y
 * los importa a wellcore_kb.exercise_metadata.
 *
 * Inferencia desde el filename:
 *   - alias = filename sin .gif (e.g. "press-banca-con-barra")
 *   - name_canonical = filename humanizado (e.g. "Press banca con barra")
 *   - muscle_primary = inferido por palabras clave (pecho, espalda, etc.)
 *   - muscle_secondary = null inicial
 *   - level_min = 'principiante' default (revisar manual)
 *   - compound_isolation = inferido (sentadilla/peso muerto/press → compound; curl/extension → isolation)
 *   - movement_pattern = inferido por keyword (squat, hinge, push_vertical, etc.)
 *   - gif_filename = el filename real (sirve para gifUrl())
 *   - gif_url_status = 'ok' (sabemos que viene del repo)
 *   - gif_url_verified_at = now()
 *
 * Idempotente: upsert por alias. Los aliases curados que ya existen (Sprint 0)
 * mantienen sus datos manuales (muscle_primary/secondary, level_min refinados).
 *
 * Solo wellcore_kb local. NO toca producción.
 */
final class KbImportExerciseCatalogCommand extends Command
{
    protected $signature = 'kb:import-exercise-catalog
                            {--dry-run : muestra qué se importaría sin escribir}
                            {--skip-curated : NO sobrescribir aliases ya curados manualmente (default)}
                            {--overwrite : sí sobrescribir incluso los curados (úsalo con cuidado)}';

    protected $description = 'Importa los 265 ejercicios del repo GitHub a exercise_metadata con inferencia automática.';

    public function handle(): int
    {
        $this->info('Descargando lista de gifs del repo...');
        $paths = $this->fetchRepoGifs();
        if ($paths === null) {
            $this->error('No pude obtener la lista de gifs del repo.');
            return 2;
        }
        $this->info('Repo contiene ' . count($paths) . ' .gif files.');

        // Dedupe por filename efectivo: si un row ya apunta a este gif_filename
        // (vía override Sprint 5 o el alias literal), considerarlo importado.
        $existingByFilename = [];
        ExerciseMetadata::query()
            ->select(['id', 'alias', 'gif_filename'])
            ->get()
            ->each(function ($row) use (&$existingByFilename): void {
                $filename = $row->gif_filename ?? ($row->alias . '.gif');
                $existingByFilename[$filename] = $row->alias;
            });
        $overwrite = (bool) $this->option('overwrite');
        $dryRun = (bool) $this->option('dry-run');

        $stats = ['imported_new' => 0, 'skipped_curated' => 0, 'updated_filename' => 0];

        foreach ($paths as $filename) {
            // Skip si este filename ya está apuntado por algún row (curated + overrides Sprint 5).
            if (isset($existingByFilename[$filename])) {
                $stats['skipped_curated']++;
                continue;
            }

            $alias = $this->aliasFromFilename($filename);

            // Caso 2: alias nuevo - inferir todos los campos
            $inferred = $this->inferFromFilename($filename);

            if ($dryRun) {
                $this->line("  + $alias ({$inferred['muscle_primary']}, {$inferred['compound_isolation']}, {$inferred['movement_pattern']})");
                $stats['imported_new']++;
                continue;
            }

            try {
                ExerciseMetadata::create([
                    'alias' => $alias,
                    'gif_filename' => $filename,
                    'name_canonical' => $inferred['name'],
                    'muscle_primary' => $inferred['muscle_primary'],
                    'muscle_secondary' => $inferred['muscle_secondary'],
                    'equipment_required' => $inferred['equipment_required'],
                    'equipment_substitutes' => [],
                    'level_min' => $inferred['level_min'],
                    'compound_isolation' => $inferred['compound_isolation'],
                    'movement_pattern' => $inferred['movement_pattern'],
                    'contraindications' => [],
                    'common_mistakes' => null,
                    'coaching_cues' => [],
                    'variations' => [],
                    'gif_url_status' => 'ok',
                    'gif_url_verified_at' => now(),
                ]);
                $stats['imported_new']++;
            } catch (Throwable $e) {
                $this->warn("Error importing '$alias': " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY-RUN] ' : '') . "Resumen:");
        $this->line("  + new imported: {$stats['imported_new']}");
        $this->line("  ~ curated existing skipped: {$stats['skipped_curated']}");

        return 0;
    }

    /**
     * @return string[]|null
     */
    private function fetchRepoGifs(): ?array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Accept' => 'application/vnd.github.v3+json'])
                ->get('https://api.github.com/repos/analyticfitness-design/wellcore-exercise-gifs/git/trees/master?recursive=1');

            if (! $response->successful()) {
                return null;
            }

            $tree = $response->json('tree') ?? [];
            $gifs = array_filter($tree, fn ($n) => str_ends_with($n['path'] ?? '', '.gif'));
            return array_values(array_map(fn ($n) => $n['path'], $gifs));
        } catch (Throwable) {
            return null;
        }
    }

    private function aliasFromFilename(string $filename): string
    {
        $base = preg_replace('/\.gif$/i', '', $filename);
        // Algunos paths del repo tienen espacios (ej. "remo-con-mancuerna-a-una mano.gif")
        // Para el alias, normalizamos: lowercase + replace spaces con guiones
        $base = strtolower($base);
        $base = preg_replace('/\s+/', '-', $base);
        $base = trim($base, '-');
        return $base;
    }

    /**
     * Infiere fields del filename usando keyword matching.
     *
     * @return array{
     *   name: string, muscle_primary: string, muscle_secondary: ?string,
     *   equipment_required: string[], level_min: string,
     *   compound_isolation: string, movement_pattern: string,
     * }
     */
    private function inferFromFilename(string $filename): array
    {
        $base = strtolower(preg_replace('/\.gif$/i', '', $filename));

        $musclePrimary = $this->inferMusclePrimary($base);
        $movementPattern = $this->inferMovementPattern($base);
        $compoundIsolation = $this->inferCompoundIsolation($base);
        $equipment = $this->inferEquipment($base);
        $levelMin = $this->inferLevelMin($base);

        return [
            'name' => $this->humanizeName($filename),
            'muscle_primary' => $musclePrimary,
            'muscle_secondary' => null,
            'equipment_required' => $equipment,
            'level_min' => $levelMin,
            'compound_isolation' => $compoundIsolation,
            'movement_pattern' => $movementPattern,
        ];
    }

    private function inferMusclePrimary(string $base): string
    {
        // Orden importa: chequear cosas específicas primero (zancada-curtsy = Glúteo)
        // antes que generales (zancada → Cuádriceps).
        $rules = [
            // Específicos primero
            'Glúteo' => ['hipthrust', 'hip-thrust', 'gluteo', 'abduccion', 'aduccion', 'patada-gluteo', 'patada-trasera', 'patada-lateral', 'cadera-acostado', 'curtsy', 'zancada-lateral', 'zancada-curtsy', 'sentadilla-sumo', 'peso-muerto-sumo', 'polea-posterior-drive'],
            'Antebrazos' => ['caminata-granjeros', 'farmer', 'curl-muñeca', 'curl-muneca', 'flexion-de-muneca', 'extension-de-muneca', 'flexiones-de-antebrazos'],
            'Trapecio' => ['encogimiento', 'shrug', 'trapecio', 'upright-row'],
            'Pantorrilla' => ['elevacion-de-talones', 'pantorrilla', 'gemelo'],

            // Pierna
            'Cuádriceps' => ['sentadilla', 'prensa', 'presa-de-piernas', 'extension-de-piernas', 'extension-cuad', 'hack-squat', 'zancada', 'lunge', 'step-up'],
            'Isquiotibiales' => ['femoral', 'rumano', 'good-morning', 'buenos-dias', 'curl-femora', 'peso-muerto'],

            // Empuje
            'Pecho' => ['press-banca', 'press-de-banca', 'press-de-pecho', 'hammer-press', 'apertura', 'fondos-de-pecho', 'fondos-pecho', 'crossover-de-pecho', 'crossover-en-maquina', 'crossover-en-polea', 'pec-deck', 'pecho', 'flexiones-de-pecho', 'flexion-de-pecho', 'flexiones-con-flexiones'],
            'Hombros' => ['press-militar', 'press-de-hombro', 'press-arnold', 'press-hombro', 'elevacion-lateral', 'elevaciones-laterales', 'elevacion-frontal', 'elevaciones-frontales', 'elevacion-fronta', 'elevacion-posterior', 'elevaciones-posteriores', 'elevacion-en-y', 'face-pull', 'facepull', 'hombro'],

            // Tirar
            'Espalda' => ['dominadas', 'jalon', 'remo', 'pullover', 'pull-over', 'pulldown', 'rack-pul', 'rack-pull', 'espalda', 'hiperextension', 'extension-de-espalda'],

            // Brazos
            'Bíceps' => ['curl-bicep', 'curl-biceps', 'curl-de-bicep', 'curl-de-biceps', 'curl-martillo', 'curl-concentrado', 'curl-predicador', 'curl-inverso', 'curl-21', 'curl-interno', 'curl-zottman', 'mancuerna-inclinado-curl', 'inclinado-curl', '-curl-'],
            'Tríceps' => ['extension-de-triceps', 'extension-triceps', 'empuje-de-triceps', 'patada-de-triceps', 'jm-press', 'fondos-de-triceps', 'fondos-en-banco', 'fondos-sentado', 'press-frances', 'kickback', 'rompecraneos', 'triceps-press', 'triceps'],

            // Core
            'Core' => ['plancha', 'abdominal', 'crunch', 'jackknife', 'elevacion-de-piernas', 'bicicleta-crunch', 'mountain-climber', 'codo-a-rodilla', 'oblicuo', 'leg-raise', 'sit-up', 'dragon-flag', 'pierna-tijera', 'giro-abdominal', 'cruzado-crunch', 'hollow', 'inclinacion-piernas', 'flexion-de-piernas-sobre-pelota', 'toque-de-talones', 'toque-talones'],

            // Cardio
            'Cardiovascular' => ['caminadora', 'eliptica', 'escaladora', 'escaladores', 'bicicleta-estatica', 'remo-ergometro', 'salto', 'burpee', 'jumping-jack'],
        ];

        foreach ($rules as $muscle => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($base, $kw)) {
                    return $muscle;
                }
            }
        }
        return 'Otro';
    }

    private function inferMovementPattern(string $base): string
    {
        if (str_contains($base, 'sentadilla') || str_contains($base, 'prensa') || str_contains($base, 'extension-de-piernas')) {
            return 'squat';
        }
        if (str_contains($base, 'peso-muerto') || str_contains($base, 'rumano') || str_contains($base, 'good-morning') || str_contains($base, 'buenos-dias') || str_contains($base, 'hipthrust') || str_contains($base, 'hip-thrust')) {
            return 'hinge';
        }
        if (str_contains($base, 'zancada') || str_contains($base, 'lunge') || str_contains($base, 'bulgara')) {
            return 'lunge';
        }
        if (str_contains($base, 'press-militar') || str_contains($base, 'press-de-hombro') || str_contains($base, 'press-arnold')) {
            return 'push_vertical';
        }
        if (str_contains($base, 'dominadas') || str_contains($base, 'jalon')) {
            return 'pull_vertical';
        }
        if (str_contains($base, 'remo') || str_contains($base, 'curl-bicep') || str_contains($base, 'curl-martillo')) {
            return 'pull_horizontal';
        }
        if (str_contains($base, 'press-banca') || str_contains($base, 'apertura') || str_contains($base, 'fondos-pecho') || str_contains($base, 'fondos-de-pecho')) {
            return 'push_horizontal';
        }
        if (str_contains($base, 'plancha') || str_contains($base, 'abdominal') || str_contains($base, 'crunch') || str_contains($base, 'oblicuo')) {
            return 'core';
        }
        if (str_contains($base, 'caminadora') || str_contains($base, 'eliptica') || str_contains($base, 'escaladora') || str_contains($base, 'bicicleta')) {
            return 'cardio_steady';
        }
        if (str_contains($base, 'burpee') || str_contains($base, 'salto') || str_contains($base, 'jumping-jack')) {
            return 'cardio_intervals';
        }
        return 'other';
    }

    private function inferCompoundIsolation(string $base): string
    {
        $compoundKeywords = [
            'sentadilla', 'peso-muerto', 'prensa', 'press-banca', 'press-de-banca',
            'press-militar', 'press-de-hombro', 'press-arnold', 'remo', 'dominadas',
            'fondos', 'jalon', 'hipthrust', 'hip-thrust', 'zancada', 'lunge',
            'rumano', 'good-morning', 'buenos-dias', 'hack-squat', 'pullover',
            'pull-up', 'burpee', 'thruster',
        ];

        foreach ($compoundKeywords as $kw) {
            if (str_contains($base, $kw)) {
                return 'compound';
            }
        }
        return 'isolation';
    }

    /**
     * @return string[]
     */
    private function inferEquipment(string $base): array
    {
        $equipment = [];
        if (str_contains($base, 'barra') || str_contains($base, '-barra')) {
            $equipment[] = 'barra';
        }
        if (str_contains($base, 'mancuerna')) {
            $equipment[] = 'mancuernas';
        }
        if (str_contains($base, 'banda')) {
            $equipment[] = 'banda_resistencia';
        }
        if (str_contains($base, 'kettlebell')) {
            $equipment[] = 'kettlebell';
        }
        if (str_contains($base, 'maquina')) {
            $equipment[] = 'maquina';
        }
        if (str_contains($base, 'polea')) {
            $equipment[] = 'maquina_polea';
        }
        if (str_contains($base, 'banco')) {
            $equipment[] = 'banco';
        }
        return $equipment;
    }

    private function inferLevelMin(string $base): string
    {
        // Avanzados: ejercicios complejos
        if (str_contains($base, 'dragon-flag') || str_contains($base, 'pistol') ||
            str_contains($base, 'muscle-up') || str_contains($base, 'planche') ||
            str_contains($base, 'sentadilla-frontal') || str_contains($base, 'snatch') ||
            str_contains($base, 'clean')) {
            return 'avanzado';
        }
        // Intermedios: compounds principales
        if (str_contains($base, 'sentadilla') || str_contains($base, 'peso-muerto') ||
            str_contains($base, 'press-militar') || str_contains($base, 'dominadas') ||
            str_contains($base, 'remo-con-barra')) {
            return 'intermedio';
        }
        return 'principiante';
    }

    private function humanizeName(string $filename): string
    {
        $base = preg_replace('/\.gif$/i', '', $filename);
        $base = str_replace(['-', '_'], ' ', $base);
        $base = preg_replace('/\s+/', ' ', $base);
        return Str::ucfirst(trim($base));
    }
}
