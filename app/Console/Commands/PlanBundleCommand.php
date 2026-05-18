<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use App\Services\ComposeEngine\ComposeEngine;
use App\Services\DecisionEngine\Data\ClientProfile;
use App\Services\DecisionEngine\DecisionEngine;
use App\Services\LintEngine\AutoFixEngine;
use App\Services\LintEngine\Data\ComposeResult as LintComposeResult;
use App\Services\LintEngine\LintEngine;
use App\Services\PersistEngine\Data\PersistInput;
use App\Services\PersistEngine\PersistService;
use Illuminate\Console\Command;
use Throwable;

/**
 * plan:bundle — orquesta el pipeline E2E para TODAS las verticales que aplican
 * a un cliente, en una sola corrida.
 *
 * Verticales aplicables por defecto:
 *   - entrenamiento (siempre)
 *   - nutricion (siempre)
 *   - suplementacion (siempre)
 *   - habitos (siempre)
 *   - ciclo (solo si gender ∈ F + tier ∈ {elite, rise})
 *
 * Para cada vertical: SELECT → COMPOSE → LINT → AUTOFIX → re-LINT → PERSIST.
 *
 * Output: tabla resumen con composed_plans.id, methodology, violations por vertical.
 *
 * Uso:
 *   php artisan plan:bundle --goal=perdida_grasa --level=intermedio --days=5 \
 *       --gender=F --tier=elite --client-handle="Cliente X"
 *
 *   --only=entrenamiento,nutricion    → solo esas verticales
 *   --skip=ciclo                       → excluir esta vertical
 *   --no-fix                           → omitir auto-fix
 *   --export-dir=path                  → exporta JSONs individuales a directorio
 */
final class PlanBundleCommand extends Command
{
    /** Verticales canónicas siempre aplicables. */
    private const ALWAYS_APPLICABLE = ['entrenamiento', 'nutricion', 'suplementacion', 'habitos'];

    /** Vertical condicional: solo F + tier elite/rise. */
    private const FEMALE_ELITE_ONLY = 'ciclo';

    protected $signature = 'plan:bundle
                            {--goal= : profile.goal compartido entre verticales}
                            {--level= : profile.level}
                            {--days= : profile.days (int)}
                            {--gender= : profile.gender}
                            {--age= : profile.age (int)}
                            {--weight= : profile.weight_kg (float)}
                            {--height= : profile.height_cm (float)}
                            {--tier= : profile.tier (trial|esencial|metodo|elite|rise)}
                            {--equipment=gym_completo : equipo disponible}
                            {--client-handle= : identificador audit del cliente}
                            {--coach-name= : nombre coach}
                            {--fecha-inicio= : YYYY-MM-DD}
                            {--only= : lista CSV de verticales a procesar (excluyente)}
                            {--skip= : lista CSV de verticales a excluir}
                            {--no-fix : omitir auto-fix}
                            {--export-dir= : graba JSONs individuales (uno por vertical)}
                            {--exclude-foods= : CSV de slugs o nombres de alimentos a excluir (ej: brocoli,zuccini,arandanos)}
                            {--meal-protein= : CSV pares slot:keyword (ej: desayuno:huevos,almuerzo:pollo,cena:tilapia)}
                            {--split= : CSV pares dia:grupo (ej: lunes:gluteo,martes:hombro+triceps+abs)}
                            {--json : output JSON estructurado en lugar de tabla}';

    protected $description = 'Pipeline E2E multi-vertical: genera 3-5 planes (entreno+nutri+supl+habitos+ciclo) para un cliente en una sola corrida.';

    public function handle(
        DecisionEngine $decision,
        ComposeEngine $compose,
        LintEngine $lint,
        AutoFixEngine $autoFix,
        PersistService $persist,
    ): int {
        $verticals = $this->resolveVerticals();
        if ($verticals === null) {
            return 2;
        }

        $fechaInicio = (string) ($this->option('fecha-inicio') ?: now()->addDay()->toDateString());
        $applyFix = ! $this->option('no-fix');
        $exportDir = $this->option('export-dir');

        if ($exportDir !== null && ! is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $results = [];
        $start = microtime(true);

        foreach ($verticals as $vertical) {
            $profile = $this->buildProfile($vertical);
            $result = $this->runVerticalPipeline(
                $decision, $compose, $lint, $autoFix, $persist,
                $profile, $fechaInicio, $applyFix, $exportDir,
            );
            $results[] = $result;
        }

        $totalDuration = (microtime(true) - $start) * 1000;

        if ($this->option('json')) {
            $this->line(json_encode([
                'total_duration_ms' => round($totalDuration, 2),
                'verticals_processed' => count($results),
                'results' => $results,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            $this->renderTable($results, $totalDuration);
        }

        // Exit 0 si todos validated, 1 si algún rejected.
        $anyRejected = array_filter($results, fn ($r) => $r['status'] === 'rejected');
        return $anyRejected === [] ? 0 : 1;
    }

    /**
     * @return string[]|null
     */
    private function resolveVerticals(): ?array
    {
        $gender = $this->option('gender');
        $tier = $this->option('tier');

        $verticals = self::ALWAYS_APPLICABLE;

        // Ciclo solo si F + Elite/Rise
        $isFemenino = $gender !== null && in_array(strtolower($gender), ['f', 'femenino', 'female', 'mujer'], true);
        $isElitePlus = in_array($tier, ['elite', 'rise'], true);
        if ($isFemenino && $isElitePlus) {
            $verticals[] = self::FEMALE_ELITE_ONLY;
        }

        // --only filtra a un subset explícito
        if ($onlyRaw = $this->option('only')) {
            $only = array_map('trim', explode(',', $onlyRaw));
            $invalid = array_diff($only, self::ALWAYS_APPLICABLE + [4 => self::FEMALE_ELITE_ONLY]);
            if ($invalid !== []) {
                $this->error('--only contiene verticales inválidas: ' . implode(', ', $invalid));
                return null;
            }
            $verticals = array_values(array_intersect($verticals, $only));
            if (in_array('ciclo', $only, true) && ! in_array('ciclo', $verticals, true)) {
                // Permitir ciclo explícito aunque no matchee F/elite
                $verticals[] = 'ciclo';
            }
        }

        // --skip excluye verticales
        if ($skipRaw = $this->option('skip')) {
            $skip = array_map('trim', explode(',', $skipRaw));
            $verticals = array_values(array_diff($verticals, $skip));
        }

        if ($verticals === []) {
            $this->error('No quedaron verticales para procesar después de --only/--skip.');
            return null;
        }

        return $verticals;
    }

    private function buildProfile(string $vertical): ClientProfile
    {
        return new ClientProfile(
            vertical: $vertical,
            goal: $this->option('goal'),
            level: $this->option('level'),
            days: $this->option('days') !== null ? (int) $this->option('days') : null,
            gender: $this->option('gender'),
            equipment: $this->option('equipment'),
            age: $this->option('age') !== null ? (int) $this->option('age') : null,
            weightKg: $this->option('weight') !== null ? (float) $this->option('weight') : null,
            heightCm: $this->option('height') !== null ? (float) $this->option('height') : null,
            tier: $this->option('tier'),
            preferences: $this->collectPreferences(),
        );
    }

    /**
     * Construye el array preferences a partir de los flags de override:
     * exclude-foods, meal-protein, split.
     */
    private function collectPreferences(): array
    {
        $prefs = [];

        if ($excludeRaw = $this->option('exclude-foods')) {
            $prefs['excluded_foods'] = array_values(array_filter(
                array_map('trim', explode(',', (string) $excludeRaw)),
            ));
        }

        if ($proteinRaw = $this->option('meal-protein')) {
            $prefs['meal_protein'] = $this->parseCsvPairs((string) $proteinRaw);
        }

        if ($splitRaw = $this->option('split')) {
            $prefs['split_override'] = $this->parseCsvPairs((string) $splitRaw);
        }

        return $prefs;
    }

    /**
     * Parsea CSV "k:v,k2:v2" → ['k' => 'v', 'k2' => 'v2'].
     */
    private function parseCsvPairs(string $raw): array
    {
        $out = [];
        foreach (explode(',', $raw) as $pair) {
            $pair = trim($pair);
            if ($pair === '' || ! str_contains($pair, ':')) {
                continue;
            }
            [$k, $v] = array_map('trim', explode(':', $pair, 2));
            if ($k === '' || $v === '') {
                continue;
            }
            $out[strtolower($k)] = $v;
        }
        return $out;
    }

    private function runVerticalPipeline(
        DecisionEngine $decision,
        ComposeEngine $compose,
        LintEngine $lint,
        AutoFixEngine $autoFix,
        PersistService $persist,
        ClientProfile $profile,
        string $fechaInicio,
        bool $applyFix,
        ?string $exportDir,
    ): array {
        $vertical = $profile->vertical;
        $start = microtime(true);

        try {
            // SELECT
            $decisionResult = $decision->decide($profile);
            $recs = $decisionResult->byVertical[$vertical] ?? [];
            if ($recs === []) {
                return $this->failureResult($vertical, 'no decision rule matched');
            }
            $methodologySlug = $recs[0]->methodologySlug;

            // COMPOSE
            $clientHandle = $this->option('client-handle');
            $coachName = $this->option('coach-name');
            $equipment = array_map('trim', explode(',', (string) ($this->option('equipment') ?: 'gym_completo')));

            $composeResult = $compose->composeForMethodology(
                $profile, $methodologySlug, $fechaInicio,
                $clientHandle, $coachName, $equipment,
            );

            // LINT pre
            $lintBefore = $lint->lint($composeResult->planJson, $vertical);

            // AUTOFIX (si aplica + hay violations con auto-fix disponible)
            $fixesApplied = [];
            $planFinal = $composeResult->planJson;
            $lintAfter = $lintBefore;
            if ($applyFix && count($lintBefore->violations) > 0) {
                $fixResult = $autoFix->applyAll($composeResult->planJson, $lintBefore->violations);
                $fixesApplied = $fixResult->appliedFixes;
                $planFinal = $fixResult->fixedPlan;
                $lintAfter = $lint->lint($planFinal, $vertical);
            }

            // Export JSON si exportDir
            $exportPath = null;
            if ($exportDir !== null) {
                $exportPath = rtrim($exportDir, '/\\') . DIRECTORY_SEPARATOR . "plan_{$vertical}.json";
                file_put_contents($exportPath, json_encode($planFinal, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }

            // PERSIST
            $composeResultPost = new \App\Services\ComposeEngine\Data\ComposeResult(
                planJson: $planFinal,
                warnings: $composeResult->warnings,
                durationMs: $composeResult->durationMs,
            );
            $audit = $persist->persist(new PersistInput(
                profile: $profile,
                methodologySlug: $methodologySlug,
                composeResult: $composeResultPost,
                lintBefore: $lintBefore,
                lintAfter: $lintAfter,
                fixesApplied: $fixesApplied,
                clientHandle: $clientHandle,
                notes: 'bundle:' . ($this->option('client-handle') ?: 'no-handle'),
                exportPath: $exportPath,
            ));

            return [
                'vertical' => $vertical,
                'methodology_slug' => $methodologySlug,
                'composed_id' => $audit->id,
                'status' => $audit->status,
                'errors' => count($lintAfter->errors()),
                'warnings' => count($lintAfter->warnings()),
                'fixes_applied' => count($fixesApplied),
                'export_path' => $exportPath,
                'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (Throwable $e) {
            return $this->failureResult($vertical, $e->getMessage());
        }
    }

    private function failureResult(string $vertical, string $reason): array
    {
        return [
            'vertical' => $vertical,
            'methodology_slug' => null,
            'composed_id' => null,
            'status' => 'rejected',
            'errors' => 1,
            'warnings' => 0,
            'fixes_applied' => 0,
            'export_path' => null,
            'duration_ms' => 0,
            'failure_reason' => $reason,
        ];
    }

    private function renderTable(array $results, float $totalDuration): void
    {
        $this->info('═══ Bundle Pipeline E2E ═══');
        $this->line(sprintf('Cliente: %s · Duración total: %.2f ms', $this->option('client-handle') ?? '?', $totalDuration));
        $this->newLine();

        $rows = [];
        foreach ($results as $r) {
            $statusIcon = match ($r['status']) {
                'validated', 'exported' => '✓',
                'rejected' => '✗',
                default => '~',
            };
            $rows[] = [
                $statusIcon . ' ' . $r['vertical'],
                $r['methodology_slug'] ?? '—',
                $r['composed_id'] ?? '—',
                $r['errors'] . '/' . $r['warnings'],
                $r['fixes_applied'],
                $r['duration_ms'] . ' ms',
                $r['status'],
            ];
        }

        $this->table(
            ['Vertical', 'Methodology', 'audit_id', 'err/warn', 'fixes', 'duración', 'status'],
            $rows,
        );

        $validated = count(array_filter($results, fn ($r) => $r['status'] === 'validated' || $r['status'] === 'exported'));
        $rejected = count(array_filter($results, fn ($r) => $r['status'] === 'rejected'));

        $this->newLine();
        $this->info("Resumen: $validated validated · $rejected rejected · " . count($results) . " totales");

        $composedIds = array_filter(array_column($results, 'composed_id'));
        if ($composedIds !== []) {
            $this->newLine();
            $this->line('Para exportar a producción (UN script con todos los inserts):');
            $this->line('   php artisan plan:export-bundle-prod-script --composed-ids=' . implode(',', $composedIds) . ' --client-id=<X> --coach-id=<Y>');
        }
    }
}
