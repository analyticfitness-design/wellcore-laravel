<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use App\Services\ComposeEngine\ComposeEngine;
use App\Services\DecisionEngine\Data\ClientProfile;
use App\Services\DecisionEngine\DecisionEngine;
use App\Services\LintEngine\AutoFixEngine;
use App\Services\LintEngine\LintEngine;
use App\Services\PersistEngine\Data\PersistInput;
use App\Services\PersistEngine\PersistService;
use Illuminate\Console\Command;
use Throwable;

/**
 * plan:replay — toma un composed_plan + permite cambiar params del profile y
 * re-ejecuta el pipeline E2E para A/B testing del motor.
 *
 * Output:
 *   - Nuevo composed_plan #N
 *   - Resumen comparativo con el original
 *   - Comando sugerido para `plan:diff <original> <nuevo>`
 *
 * Uso:
 *   php artisan plan:replay 1 --goal=recomposicion       # cambia solo el goal
 *   php artisan plan:replay 1 --days=4 --level=avanzado  # múltiples overrides
 *   php artisan plan:replay 1 --gender=M --tier=elite    # cambia perfil F→M
 *
 * Útil para:
 *   - A/B testing: "¿qué pasa si bajo el level a principiante?"
 *   - Detectar sensibilidad del motor: "¿hipertrofia vs recomposicion cambia mucho el plan?"
 *   - Refinar perfiles: "¿qué cambia entre 4 días y 5 días?"
 */
final class PlanReplayCommand extends Command
{
    protected $signature = 'plan:replay
                            {composed_id : ID original en wellcore_kb.composed_plans}
                            {--goal= : override profile.goal}
                            {--level= : override profile.level}
                            {--days= : override profile.days}
                            {--gender= : override profile.gender}
                            {--tier= : override profile.tier}
                            {--client-handle= : nuevo client_handle (default = original + "-replay")}
                            {--no-fix : omitir auto-fix}
                            {--json : output JSON estructurado}';

    protected $description = 'Re-ejecuta el pipeline E2E para un composed_plan con overrides del profile (A/B testing).';

    public function handle(
        DecisionEngine $decision,
        ComposeEngine $compose,
        LintEngine $lint,
        AutoFixEngine $autoFix,
        PersistService $persist,
    ): int {
        $id = (int) $this->argument('composed_id');
        $original = ComposedPlan::find($id);
        if (! $original) {
            $this->error("composed_plans #$id no encontrado.");
            return 2;
        }

        $originalProfile = $original->profile_json ?? [];

        // Build new profile con overrides
        $newProfileData = array_filter([
            'vertical' => $originalProfile['vertical'] ?? null,
            'goal' => $this->option('goal') ?? ($originalProfile['goal'] ?? null),
            'level' => $this->option('level') ?? ($originalProfile['level'] ?? null),
            'days' => $this->option('days') !== null ? (int) $this->option('days') : ($originalProfile['days'] ?? null),
            'gender' => $this->option('gender') ?? ($originalProfile['gender'] ?? null),
            'tier' => $this->option('tier') ?? ($originalProfile['tier'] ?? null),
        ], fn ($v) => $v !== null);

        $changes = $this->detectChanges($originalProfile, $newProfileData);
        if ($changes === []) {
            $this->warn('Sin overrides — usá --goal/--level/--days/--gender/--tier para cambiar algo.');
            return 0;
        }

        $newProfile = ClientProfile::fromArray($newProfileData);
        $clientHandle = $this->option('client-handle') ?? (($original->client_handle ?? 'replay') . '-replay');
        $fechaInicio = now()->addDay()->toDateString();
        $applyFix = ! $this->option('no-fix');

        $this->info('═══ plan:replay ═══');
        $this->line("Original: #$id — {$original->methodology_slug} — handle={$original->client_handle}");
        $this->info('Overrides aplicados:');
        foreach ($changes as $field => $vals) {
            $this->line("  · $field: {$vals['old']} → {$vals['new']}");
        }
        $this->newLine();

        try {
            // SELECT
            $decisionResult = $decision->decide($newProfile);
            $recs = $decisionResult->byVertical[$newProfile->vertical] ?? [];
            if ($recs === []) {
                $this->error('DecisionEngine no encontró methodology para el nuevo profile.');
                return 1;
            }
            $methodologySlug = $recs[0]->methodologySlug;
            $this->info('[SELECT] ' . $methodologySlug . " (confidence {$recs[0]->confidence})");

            // COMPOSE
            $composeResult = $compose->composeForMethodology(
                $newProfile, $methodologySlug, $fechaInicio,
                $clientHandle, $original->client_handle ? 'replay-coach' : null, ['gym_completo'],
            );

            // LINT pre
            $lintBefore = $lint->lint($composeResult->planJson, $newProfile->vertical);

            // AUTOFIX
            $fixesApplied = [];
            $planFinal = $composeResult->planJson;
            $lintAfter = $lintBefore;
            if ($applyFix && count($lintBefore->violations) > 0) {
                $fixResult = $autoFix->applyAll($composeResult->planJson, $lintBefore->violations);
                $fixesApplied = $fixResult->appliedFixes;
                $planFinal = $fixResult->fixedPlan;
                $lintAfter = $lint->lint($planFinal, $newProfile->vertical);
            }

            // PERSIST
            $composeResultPost = new \App\Services\ComposeEngine\Data\ComposeResult(
                planJson: $planFinal,
                warnings: $composeResult->warnings,
                durationMs: $composeResult->durationMs,
            );
            $audit = $persist->persist(new PersistInput(
                profile: $newProfile,
                methodologySlug: $methodologySlug,
                composeResult: $composeResultPost,
                lintBefore: $lintBefore,
                lintAfter: $lintAfter,
                fixesApplied: $fixesApplied,
                clientHandle: $clientHandle,
                notes: "replay of #$id",
            ));

            $this->newLine();
            $this->info('Resumen comparativo:');
            $this->line(sprintf('  Original #%d: methodology=%s · violations=%d',
                $original->id, $original->methodology_slug, $original->violations_after));
            $this->line(sprintf('  Replay   #%d: methodology=%s · violations=%d',
                $audit->id, $audit->methodology_slug, $audit->violations_after));
            $methodologyChanged = $original->methodology_slug !== $audit->methodology_slug;
            $this->line(sprintf('  Methodology cambió: %s', $methodologyChanged ? '✓ SÍ' : '✗ NO'));
            $this->newLine();
            $this->info('Para ver diff completo:');
            $this->line("  php artisan plan:diff $id {$audit->id}");

            if ($this->option('json')) {
                $this->newLine();
                $this->line(json_encode([
                    'original_id' => $id,
                    'replay_id' => $audit->id,
                    'changes' => $changes,
                    'methodology_changed' => $methodologyChanged,
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }

            return 0;
        } catch (Throwable $e) {
            $this->error('Pipeline falló: ' . $e->getMessage());
            return 2;
        }
    }

    /**
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private function detectChanges(array $orig, array $new): array
    {
        $changes = [];
        foreach ($new as $k => $vNew) {
            $vOld = $orig[$k] ?? null;
            if ($vOld != $vNew) {
                $changes[$k] = ['old' => $vOld ?? 'null', 'new' => $vNew];
            }
        }
        return $changes;
    }
}
