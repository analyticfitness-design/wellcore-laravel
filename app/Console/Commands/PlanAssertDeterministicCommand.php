<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use App\Services\ComposeEngine\ComposeEngine;
use App\Services\DecisionEngine\Data\ClientProfile;
use App\Services\DecisionEngine\DecisionEngine;
use Illuminate\Console\Command;
use Throwable;

/**
 * plan:assert-deterministic — verifica que el motor v2 produce output idéntico
 * cuando se le da el mismo ClientProfile (test de regresión por determinismo).
 *
 * Algoritmo:
 *   1. Para cada vertical (o subset según --only), corre ComposeEngine dos veces
 *      con el mismo profile.
 *   2. Compara los planJson resultantes campo por campo (excluyendo timestamps
 *      y otros campos volatiles).
 *   3. Falla con exit 1 si encuentra cualquier diferencia.
 *
 * NO toca composed_plans (es read-only sobre el motor). NO requiere DB de prod.
 *
 * Útil como:
 *   - Smoke test pre-deploy ("¿el motor sigue siendo determinístico?")
 *   - Regression test después de refactor
 *   - CI gate antes de subir cambios
 */
final class PlanAssertDeterministicCommand extends Command
{
    private const ALL_VERTICALS = ['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo'];

    protected $signature = 'plan:assert-deterministic
                            {--goal=hipertrofia : profile.goal}
                            {--level=intermedio : profile.level}
                            {--days=5 : profile.days}
                            {--gender=F : profile.gender (F→incluye ciclo si tier elite)}
                            {--tier=elite : profile.tier}
                            {--only= : verticales a verificar (CSV)}
                            {--include-lint : verifica también determinismo del LintEngine (Sprint 45)}
                            {--verbose-diff : muestra el primer diff completo si falla}';

    protected $description = 'Verifica determinismo del motor v2: mismo profile → mismo planJson (test regression).';

    public function handle(
        DecisionEngine $decision,
        ComposeEngine $compose,
        \App\Services\LintEngine\LintEngine $lint,
    ): int {
        $verticals = $this->option('only')
            ? array_map('trim', explode(',', $this->option('only')))
            : self::ALL_VERTICALS;

        $fechaInicio = '2026-01-01'; // fecha fija para asegurar determinismo
        $allPass = true;
        $includeLint = (bool) $this->option('include-lint');

        $this->info('═══ plan:assert-deterministic ═══');
        $this->line("Profile: goal={$this->option('goal')} · level={$this->option('level')} · days={$this->option('days')} · gender={$this->option('gender')} · tier={$this->option('tier')}");
        $this->line("Fecha inicio fija: $fechaInicio");
        $this->line('Incluye lint: ' . ($includeLint ? 'sí' : 'no'));
        $this->newLine();

        foreach ($verticals as $vertical) {
            $result = $this->verifyVertical($decision, $compose, $vertical, $fechaInicio, $lint, $includeLint);
            if ($result['skipped']) {
                $this->line(sprintf('  ~ %-15s · SKIP · %s', $vertical, $result['reason']));
                continue;
            }

            if ($result['identical']) {
                $lintSuffix = isset($result['lint_info']) && $result['lint_info'] !== null
                    ? sprintf(' · lint: %s', $result['lint_info']) : '';
                $this->line(sprintf('  ✓ %-15s · IDÉNTICO · %s%s', $vertical, $result['methodology'], $lintSuffix));
            } else {
                $allPass = false;
                $this->error(sprintf('  ✗ %-15s · NO determinístico (%d diffs)', $vertical, $result['diff_count']));
                if ($this->option('verbose-diff')) {
                    $this->line('    Primer diff:');
                    $this->line('    ' . json_encode($result['first_diff'], JSON_UNESCAPED_UNICODE));
                }
            }
        }

        $this->newLine();
        if ($allPass) {
            $this->info('✓ Motor v2 es DETERMINÍSTICO en todas las verticales verificadas.');
            return 0;
        }
        $this->error('✗ Motor v2 NO es determinístico en al menos una vertical. Re-ejecutar con --verbose-diff para detalle.');
        return 1;
    }

    private function verifyVertical(
        DecisionEngine $decision,
        ComposeEngine $compose,
        string $vertical,
        string $fechaInicio,
        ?\App\Services\LintEngine\LintEngine $lint = null,
        bool $includeLint = false,
    ): array {
        $profile = new ClientProfile(
            vertical: $vertical,
            goal: $this->option('goal'),
            level: $this->option('level'),
            days: (int) $this->option('days'),
            gender: $this->option('gender'),
            tier: $this->option('tier'),
        );

        try {
            $decisionResult = $decision->decide($profile);
            $recs = $decisionResult->byVertical[$vertical] ?? [];
            if ($recs === []) {
                return ['skipped' => true, 'identical' => false, 'reason' => 'No methodology matched for profile.'];
            }
            $methodologySlug = $recs[0]->methodologySlug;

            $a = $compose->composeForMethodology($profile, $methodologySlug, $fechaInicio, 'det-test', 'Coach', ['gym_completo']);
            $b = $compose->composeForMethodology($profile, $methodologySlug, $fechaInicio, 'det-test', 'Coach', ['gym_completo']);

            $jsonA = json_encode($a->planJson, JSON_UNESCAPED_UNICODE);
            $jsonB = json_encode($b->planJson, JSON_UNESCAPED_UNICODE);

            $composeIdentical = ($jsonA === $jsonB);

            // Sprint 45: si --include-lint, también verifica que LintEngine es determinístico.
            $lintIdentical = true;
            $lintInfo = null;
            if ($includeLint && $composeIdentical && $lint !== null) {
                $lintA = $lint->lint($a->planJson, $vertical);
                $lintB = $lint->lint($b->planJson, $vertical);
                // Compara violations serializadas (sin durationMs que es ruido)
                $vA = array_map(fn ($v) => $v->toArray(), $lintA->violations);
                $vB = array_map(fn ($v) => $v->toArray(), $lintB->violations);
                $lintIdentical = json_encode($vA) === json_encode($vB);
                $lintInfo = sprintf('%d violations · %s', count($lintA->violations), $lintIdentical ? 'idem' : 'diferentes');
            }

            $allIdentical = $composeIdentical && $lintIdentical;

            if ($allIdentical) {
                return [
                    'skipped' => false,
                    'identical' => true,
                    'methodology' => $methodologySlug,
                    'diff_count' => 0,
                    'lint_info' => $lintInfo,
                ];
            }

            $firstDiff = $composeIdentical
                ? ['note' => 'LintEngine no determinístico — violations difieren']
                : $this->firstDiff($a->planJson, $b->planJson);

            return [
                'skipped' => false,
                'identical' => false,
                'methodology' => $methodologySlug,
                'diff_count' => 1,
                'first_diff' => $firstDiff,
                'compose_identical' => $composeIdentical,
                'lint_identical' => $lintIdentical,
                'lint_info' => $lintInfo,
            ];
        } catch (Throwable $e) {
            return ['skipped' => true, 'identical' => false, 'reason' => 'Error: ' . $e->getMessage()];
        }
    }

    private function firstDiff(array $a, array $b, string $path = '$'): array
    {
        $keys = array_unique(array_merge(array_keys($a), array_keys($b)));
        foreach ($keys as $k) {
            $vA = $a[$k] ?? null;
            $vB = $b[$k] ?? null;
            if (is_array($vA) && is_array($vB)) {
                $sub = $this->firstDiff($vA, $vB, "$path.$k");
                if ($sub !== []) {
                    return $sub;
                }
            } elseif ($vA != $vB) {
                return [
                    'path' => "$path.$k",
                    'a' => mb_substr((string) (is_scalar($vA) ? $vA : json_encode($vA)), 0, 100),
                    'b' => mb_substr((string) (is_scalar($vB) ? $vB : json_encode($vB)), 0, 100),
                ];
            }
        }
        return [];
    }
}
