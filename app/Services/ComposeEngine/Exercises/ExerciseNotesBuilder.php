<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Exercises;

use App\Models\Kb\ExerciseMetadata;

/**
 * Genera notas técnicas humanas para cada ejercicio del plan.
 *
 * Estrategia de fallback en cascada:
 *   1. Si `exercise_metadata.coaching_cues` está poblado → usa los cues como notas
 *      (formato: "Cue1. Cue2. Cue3.")
 *   2. Si NO hay cues pero hay `movement_pattern` + `muscle_primary` → genera nota inteligente
 *      desde plantilla por patrón ("Para hip_thrust de gluteos: ...")
 *   3. Fallback genérico por nivel ("Mantené técnica controlada. Bajá 2s, subí 1s.")
 *
 * Idem `common_mistakes` y `variations` — fallbacks si la BD está vacía.
 *
 * Esto cierra el gap detectado: 220 exercise_metadata pero solo 25/220 con cues poblados.
 * El composer producirá notas decentes aunque la BD no esté curada al 100%.
 */
final class ExerciseNotesBuilder
{
    /**
     * Plantillas de cues por movement_pattern (fallback inteligente sin BD).
     *
     * @var array<string, string[]>
     */
    private const PATTERN_FALLBACKS = [
        'squat' => [
            'Bajá controlado hasta paralelo (muslos a 90°)',
            'Rodillas siguen a los pies, no se meten hacia adentro',
            'Pecho arriba, core activado, peso en talones',
        ],
        'hinge' => [
            'Cadera atrás como cerrando una puerta con la cola',
            'Barra rozando las piernas en todo el recorrido',
            'Espalda neutra, core firme. Subí extendiendo cadera',
        ],
        'push_horizontal' => [
            'Escápulas retraídas y abajo',
            'Bajá 2-3 seg controlado, empujá explosivo',
            'Codos 45° del torso, no abiertos a 90°',
        ],
        'push_vertical' => [
            'Codos bajo la barra (no por delante)',
            'Glúteo y abdomen firmes — no arquear lumbar',
            'Empujá vertical, no hacia adelante',
        ],
        'pull_horizontal' => [
            'Tirá con los codos hacia atrás, no con bíceps',
            'Apretá escápulas al final del movimiento',
            'Pecho arriba, hombros lejos de las orejas',
        ],
        'pull_vertical' => [
            'Manos pronas o supinas según variante — agarrá fuerte',
            'Tirá llevando los codos al piso, no con los brazos',
            'Bajá controlado en 2s, no te dejes caer',
        ],
        'hip_thrust' => [
            'Espalda apoyada en banco, barra acolchada en caderas',
            'Empujá desde talones, apretá glúteo 1 seg arriba',
            'Costillas abajo, no extender lumbar',
        ],
        'lunge' => [
            'Paso largo, rodilla trasera casi al piso',
            'Rodilla delantera no pasa la punta del pie',
            'Subí empujando con el talón delantero',
        ],
        'isolation_arm' => [
            'Codos quietos al lado del cuerpo',
            'Movimiento solo del codo, sin balanceo',
            'Bajá controlado 2s, subí explosivo',
        ],
        'isolation_shoulder' => [
            'Codos ligeramente flexionados',
            'Subí hasta línea de hombros, no más arriba',
            'Bajá controlado, sin caer',
        ],
        'isolation_leg' => [
            'Rango completo de movimiento',
            'Apretá el músculo objetivo (glúteo o cuádriceps) en cada rep',
            'Bajá 2s controlado, subí explosivo',
        ],
        'glute_iso' => [
            'Cadera estable, no rotes el tronco',
            'Apretá el glúteo 1 segundo en el pico del movimiento',
            'Movimiento controlado, sin balanceo ni momentum',
        ],
        'leg_curl' => [
            'Cadera fija contra la máquina',
            'Bajá controlado 2-3 segundos, no dejes caer el peso',
            'Apretá el femoral en el pico de la contracción',
        ],
        'leg_extension' => [
            'Sentate firme, espalda apoyada',
            'Extendé completamente, apretá el cuádriceps 1 seg arriba',
            'Bajá controlado, sin soltar el peso',
        ],
        'isolation_chest' => [
            'Codos ligeramente flexionados (no rígidos)',
            'Sentí el estiramiento abajo, apretá el pecho arriba',
            'Bajá controlado 2-3s, subí explosivo apretando el pecho',
        ],
        'core' => [
            'Activá el core antes de iniciar el movimiento',
            'Respirá: exhalá en el esfuerzo',
            'Sin balanceo, movimiento controlado',
        ],
        'cardio' => [
            'Ritmo constante, respiración nasal cuando puedas',
            'Postura erguida, pasos cortos al inicio',
            'Si no podés mantener conversación → bajá intensidad',
        ],
    ];

    /**
     * Plantillas de errores comunes por movement_pattern (fallback).
     *
     * @var array<string, string>
     */
    private const MISTAKES_FALLBACKS = [
        'squat' => 'Bajada parcial (no llega a paralelo) · Rodillas hacia adentro · Inclinarse mucho hacia adelante',
        'hinge' => 'Curvar la espalda · Doblar mucho las rodillas (convierte en squat) · Bajar el peso sin control',
        'push_horizontal' => 'Codos abiertos a 90° · Rebotar la barra en el pecho · Rango parcial sin tocar pecho',
        'push_vertical' => 'Arquear la lumbar · Empujar hacia adelante · No bajar al rango completo',
        'pull_horizontal' => 'Usar momentum con el torso · No retraer escápulas · Codos abiertos',
        'pull_vertical' => 'Tirar con los brazos en vez de la espalda · No bajar al rango completo · Soltar al bajar',
        'hip_thrust' => 'Levantar la lumbar (no apretar glúteo) · Pies muy adelante · No apretar arriba',
        'lunge' => 'Rodilla trasera no baja · Tronco se inclina · Paso muy corto (sobrecarga rodilla)',
        'isolation_arm' => 'Balancear el cuerpo · Codos se mueven · Rango parcial',
        'isolation_shoulder' => 'Subir más allá de los hombros · Codos bloqueados rectos · Usar trapecio',
        'isolation_leg' => 'Rango parcial · No apretar el músculo · Usar peso excesivo y romper técnica',
        'glute_iso' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
        'leg_curl' => 'Cadera se levanta · Bajada descontrolada · Usar momentum',
        'leg_extension' => 'Bloquear rodillas arriba · Inclinarse atrás para ayudar · Bajada descontrolada',
        'isolation_chest' => 'Codos rectos y rígidos · Bajar demasiado el peso · Rebotar al subir',
        'core' => 'Tirar con el cuello · Apretar lumbar · No respirar',
    ];

    /**
     * Genera el bloque `notas` y `errores_comunes` para un ejercicio.
     *
     * @return array{notas: string, errores_comunes: string|null, tecnica_ejecucion: string|null}
     */
    public function buildFor(ExerciseMetadata $exercise): array
    {
        $cues = $this->resolveCues($exercise);
        $mistakes = $this->resolveMistakes($exercise);

        return [
            // Nota lectura rápida (2-3 cues, ~1-2 líneas en la UI cliente)
            'notas' => $this->joinCues(array_slice($cues, 0, 3)),
            // Técnica de ejecución completa — todos los cues concatenados.
            'tecnica_ejecucion' => $this->joinCues($cues),
            // Errores típicos a evitar.
            'errores_comunes' => $mistakes,
        ];
    }

    /**
     * Resuelve la primera variación canónica del ejercicio si existe.
     *
     * @return array{nombre: string, gif_url: string, motivo: string|null}|null
     */
    public function resolveFirstVariation(ExerciseMetadata $exercise): ?array
    {
        $variations = is_array($exercise->variations) ? $exercise->variations : [];
        if ($variations === []) {
            return null;
        }

        $first = $variations[0];
        $alias = is_array($first) ? ($first['alias'] ?? null) : null;
        if (! $alias) {
            return null;
        }

        $variationModel = ExerciseMetadata::where('alias', $alias)->first();
        if (! $variationModel || ! $variationModel->gif_filename) {
            return null;
        }

        return [
            'nombre' => $variationModel->name_canonical,
            'gif_url' => $variationModel->gifUrl(),
            'motivo' => is_array($first) ? ($first['reason'] ?? null) : null,
        ];
    }

    /**
     * Devuelve la lista de cues. Si la BD tiene cues, los usa. Si no, falls back a movement_pattern.
     *
     * @return string[]
     */
    private function resolveCues(ExerciseMetadata $exercise): array
    {
        $stored = is_array($exercise->coaching_cues) ? $exercise->coaching_cues : [];
        $stored = array_filter(array_map('trim', $stored));
        if ($stored !== []) {
            return array_values($stored);
        }

        // Fallback inteligente por pattern.
        $pattern = $this->normalizePattern($exercise);
        return self::PATTERN_FALLBACKS[$pattern] ?? self::PATTERN_FALLBACKS['isolation_arm'];
    }

    private function resolveMistakes(ExerciseMetadata $exercise): ?string
    {
        $stored = is_string($exercise->common_mistakes) ? trim($exercise->common_mistakes) : null;
        if ($stored !== null && $stored !== '') {
            return $stored;
        }

        $pattern = $this->normalizePattern($exercise);
        return self::MISTAKES_FALLBACKS[$pattern] ?? null;
    }

    /**
     * Normaliza movement_pattern. Cascada de inferencia:
     *   1. Nombre del ejercicio (alias) — keywords precisos
     *   2. movement_pattern de BD
     *   3. compound_isolation + muscle_primary
     */
    private function normalizePattern(ExerciseMetadata $exercise): string
    {
        $alias = mb_strtolower((string) $exercise->alias, 'UTF-8');
        $name = mb_strtolower((string) $exercise->name_canonical, 'UTF-8');
        $haystack = $alias . ' ' . $name;

        // 1. Inferencia por nombre — más precisa que movement_pattern
        $byName = $this->inferPatternFromName($haystack);
        if ($byName !== null) {
            return $byName;
        }

        // 2. movement_pattern de BD si está en nuestro catálogo
        $pattern = is_string($exercise->movement_pattern) ? trim($exercise->movement_pattern) : '';
        if ($pattern !== '' && isset(self::PATTERN_FALLBACKS[$pattern])) {
            return $pattern;
        }

        // 3. Fallback por compound vs isolation + muscle_primary
        $muscle = mb_strtolower((string) $exercise->muscle_primary, 'UTF-8');
        $isCompound = $exercise->compound_isolation === 'compound';

        if (! $isCompound) {
            return match (true) {
                str_contains($muscle, 'biceps'), str_contains($muscle, 'triceps') => 'isolation_arm',
                str_contains($muscle, 'hombro'), str_contains($muscle, 'deltoide') => 'isolation_shoulder',
                str_contains($muscle, 'cuadricep'), str_contains($muscle, 'femoral'), str_contains($muscle, 'gluteo'), str_contains($muscle, 'pierna') => 'isolation_leg',
                str_contains($muscle, 'abdomen'), str_contains($muscle, 'core') => 'core',
                default => 'isolation_arm',
            };
        }

        // Compounds: inferir por muscle group
        return match (true) {
            str_contains($muscle, 'femoral'), str_contains($muscle, 'lumbar'), str_contains($muscle, 'isquio') => 'hinge',
            str_contains($muscle, 'cuadricep'), str_contains($muscle, 'gluteo') => 'squat',
            str_contains($muscle, 'pecho') => 'push_horizontal',
            str_contains($muscle, 'hombro'), str_contains($muscle, 'deltoide') => 'push_vertical',
            str_contains($muscle, 'espalda'), str_contains($muscle, 'dorsal'), str_contains($muscle, 'remo') => 'pull_horizontal',
            default => 'push_horizontal',
        };
    }

    /**
     * Infiere pattern por keywords del nombre del ejercicio.
     * Más preciso que muscle_primary porque captura tipo de movimiento real.
     */
    private function inferPatternFromName(string $haystack): ?string
    {
        // Hip thrust y variantes (incluso "hip thrust una pierna")
        if (str_contains($haystack, 'hip thrust') || str_contains($haystack, 'hipthrust')
            || str_contains($haystack, 'puente gluteo') || str_contains($haystack, 'puente-gluteo')) {
            return 'hip_thrust';
        }
        // Peso muerto y variantes
        if (str_contains($haystack, 'peso muerto') || str_contains($haystack, 'deadlift')
            || str_contains($haystack, 'rumano') || str_contains($haystack, 'good morning')) {
            return 'hinge';
        }
        // Sentadilla y variantes
        if (str_contains($haystack, 'sentadilla') || str_contains($haystack, 'squat')
            || str_contains($haystack, 'prensa') || str_contains($haystack, 'hack')) {
            return 'squat';
        }
        // Lunge / zancada / bulgara
        if (str_contains($haystack, 'zancada') || str_contains($haystack, 'lunge')
            || str_contains($haystack, 'bulgara') || str_contains($haystack, 'bulgaro')
            || str_contains($haystack, 'step up') || str_contains($haystack, 'step-up')) {
            return 'lunge';
        }
        // Abducción / Aducción / Patada glúteo / Kickback / Extensión cadera
        if (str_contains($haystack, 'abduccion') || str_contains($haystack, 'aduccion')
            || str_contains($haystack, 'patada') || str_contains($haystack, 'kickback')
            || str_contains($haystack, 'extension cadera') || str_contains($haystack, 'puente gluteo isometrico')
            || str_contains($haystack, 'donkey kick') || str_contains($haystack, 'fire hydrant')) {
            return 'glute_iso';
        }
        // Curl femoral / leg curl
        if (str_contains($haystack, 'curl femoral') || str_contains($haystack, 'curl pierna')
            || str_contains($haystack, 'leg curl') || str_contains($haystack, 'femoral acostado')
            || str_contains($haystack, 'femoral sentado') || str_contains($haystack, 'nordic curl')) {
            return 'leg_curl';
        }
        // Extensión cuádriceps / leg extension
        if (str_contains($haystack, 'extension cuadriceps') || str_contains($haystack, 'extension pierna')
            || str_contains($haystack, 'leg extension')) {
            return 'leg_extension';
        }
        // Curl bíceps
        if (str_contains($haystack, 'curl biceps') || str_contains($haystack, 'curl alterno')
            || str_contains($haystack, 'curl predicador') || str_contains($haystack, 'curl martillo')
            || str_contains($haystack, 'curl spider') || str_contains($haystack, 'curl 21')) {
            return 'isolation_arm';
        }
        // Extension tríceps
        if (str_contains($haystack, 'extension triceps') || str_contains($haystack, 'triceps polea')
            || str_contains($haystack, 'triceps frances') || str_contains($haystack, 'press frances')
            || str_contains($haystack, 'patada triceps') || str_contains($haystack, 'overhead triceps')) {
            return 'isolation_arm';
        }
        // Elevación lateral / frontal / posterior (hombros isolation)
        if (str_contains($haystack, 'elevacion lateral') || str_contains($haystack, 'elevacion frontal')
            || str_contains($haystack, 'pajaro') || str_contains($haystack, 'face pull') || str_contains($haystack, 'face-pull')
            || str_contains($haystack, 'rear delt')) {
            return 'isolation_shoulder';
        }
        // Press hombro / militar
        if (str_contains($haystack, 'press militar') || str_contains($haystack, 'press hombro')
            || str_contains($haystack, 'press arnold') || str_contains($haystack, 'overhead press')) {
            return 'push_vertical';
        }
        // Press banca / pecho
        if (str_contains($haystack, 'press banca') || str_contains($haystack, 'press de banca')
            || str_contains($haystack, 'press pecho') || str_contains($haystack, 'bench press')) {
            return 'push_horizontal';
        }
        // Aperturas y cruces de pecho (isolation pecho)
        if (str_contains($haystack, 'aperturas') || str_contains($haystack, 'cruces')
            || str_contains($haystack, 'pec deck') || str_contains($haystack, 'fly')
            || str_contains($haystack, 'cable cross')) {
            return 'isolation_chest';
        }
        // Remo / pull horizontal
        if (str_contains($haystack, 'remo') || str_contains($haystack, 'row')) {
            return 'pull_horizontal';
        }
        // Dominadas / jalón / pulldown / pull-up
        if (str_contains($haystack, 'dominada') || str_contains($haystack, 'jalon')
            || str_contains($haystack, 'pulldown') || str_contains($haystack, 'pull up')
            || str_contains($haystack, 'pull-up') || str_contains($haystack, 'chin up')) {
            return 'pull_vertical';
        }
        // Plancha / core
        if (str_contains($haystack, 'plancha') || str_contains($haystack, 'crunch')
            || str_contains($haystack, 'abdominal') || str_contains($haystack, 'sit up')
            || str_contains($haystack, 'sit-up') || str_contains($haystack, 'leg raise')
            || str_contains($haystack, 'mountain climber') || str_contains($haystack, 'russian twist')) {
            return 'core';
        }
        // Cardio
        if (str_contains($haystack, 'caminadora') || str_contains($haystack, 'escaladora')
            || str_contains($haystack, 'bici') || str_contains($haystack, 'eliptica')
            || str_contains($haystack, 'remo cardio') || str_contains($haystack, 'jumping')
            || str_contains($haystack, 'burpee') || str_contains($haystack, 'salto')
            || str_contains($haystack, 'sprint')) {
            return 'cardio';
        }

        return null;
    }

    /**
     * @param string[] $cues
     */
    private function joinCues(array $cues): ?string
    {
        if ($cues === []) {
            return null;
        }
        // Junta cues con punto y espacio. Cierra con punto si no lo tiene.
        $joined = implode('. ', array_map(fn (string $c) => rtrim($c, '. '), $cues));
        if (! str_ends_with($joined, '.')) {
            $joined .= '.';
        }
        return $joined;
    }
}
