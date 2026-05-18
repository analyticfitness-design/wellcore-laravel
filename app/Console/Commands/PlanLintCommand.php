<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AssignedPlan;
use App\Services\LintEngine\AutoFixEngine;
use App\Services\LintEngine\Data\AppliedFix;
use App\Services\LintEngine\Data\FixResult;
use App\Services\LintEngine\Data\LintResult;
use App\Services\LintEngine\Data\Violation;
use App\Services\LintEngine\LintEngine;
use Illuminate\Console\Command;
use JsonException;

/**
 * plan:lint — corre el linter del motor v2 contra un JSON de plan.
 *
 * Modos:
 *   php artisan plan:lint --file=docs/audit-motor-v2/sample.json
 *       Lee el archivo y lo lintea (no toca DB de producción).
 *
 *   php artisan plan:lint --id=123
 *       Lee assigned_plans.id=123 desde producción y lintea su content.
 *
 *   php artisan plan:lint --client=78 --plan-type=entrenamiento
 *       Lee el plan ACTIVO del cliente y tipo dado.
 *
 *   php artisan plan:lint --batch=esencial --limit=10
 *       Corre el linter contra los últimos 10 planes activos del tier dado
 *       y reporta agregado (cobertura retroactiva).
 *
 * Exit codes:
 *   0 = sin violations o solo info
 *   1 = warnings presentes
 *   2 = al menos 1 error (plan no pasaría VALIDATE)
 *   3 = error de configuración (file no existe, JSON malformado, etc.)
 */
final class PlanLintCommand extends Command
{
    protected $signature = 'plan:lint
                            {--file= : Path a un archivo JSON con el plan}
                            {--id= : ID de assigned_plans en producción}
                            {--client= : client_id (combinar con --plan-type)}
                            {--plan-type= : entrenamiento|nutricion|suplementacion|habitos|ciclo}
                            {--batch= : tier para batch mode (esencial|metodo|elite|rise|presencial)}
                            {--limit=10 : límite para batch mode}
                            {--vertical= : override de vertical para el linter (auto-detecta si no se pasa)}
                            {--json : output en formato JSON parseable}
                            {--fix : aplica auto-fixes y re-lintea (writes file.fixed.json si --file)}
                            {--no-cache : fuerza HEAD HTTP real saltando el DB cache de gif_url_status (útil en CI)}';

    protected $description = 'Corre el linter del motor v2 contra un plan (file, ID, cliente, o batch).';

    public function handle(LintEngine $engine, AutoFixEngine $fixer): int
    {
        if ($this->option('no-cache')) {
            // Resolve el validator del registry y forzar bypass del DB cache.
            $registry = app(\App\Services\LintEngine\ValidatorRegistry::class);
            if ($registry->has('external_head')) {
                $validator = $registry->get('external_head');
                if ($validator instanceof \App\Services\LintEngine\Validators\ExternalHeadValidator) {
                    $validator->forceNoDbCache(true);
                }
            }
        }
        $cases = $this->buildCases();
        if ($cases === null) {
            return self::FAILURE;
        }

        if (count($cases) === 0) {
            $this->error('No se encontraron planes para evaluar.');
            return 3;
        }

        $isJson = (bool) $this->option('json');
        $isBatch = count($cases) > 1;
        $aggregated = ['errors' => 0, 'warnings' => 0, 'infos' => 0, 'evaluated' => 0, 'passes' => 0, 'fails' => 0];
        $worstExit = 0;
        $jsonOutput = [];

        $applyFix = (bool) $this->option('fix');

        foreach ($cases as $case) {
            $result = $engine->lint($case['plan'], $case['vertical']);

            $fixResult = null;
            $reLintResult = null;
            $finalPlan = $case['plan'];

            if ($applyFix && count($result->violations) > 0) {
                $fixResult = $fixer->applyAll($case['plan'], $result->violations);
                $finalPlan = $fixResult->fixedPlan;
                // Re-lint con el plan corregido
                $reLintResult = $engine->lint($finalPlan, $case['vertical']);
            }

            $authoritativeResult = $reLintResult ?? $result;
            $worstExit = max($worstExit, $authoritativeResult->exitCode());

            $aggregated['errors'] += count($authoritativeResult->errors());
            $aggregated['warnings'] += count($authoritativeResult->warnings());
            $aggregated['infos'] += count($authoritativeResult->infos());
            $aggregated['evaluated'] += $authoritativeResult->rulesEvaluated;
            $authoritativeResult->passes() ? $aggregated['passes']++ : $aggregated['fails']++;

            if ($applyFix && isset($case['file_path']) && $fixResult !== null && $fixResult->applied() > 0) {
                $this->writeFixedFile($case['file_path'], $finalPlan);
            }

            if ($isJson) {
                $entry = [
                    'label' => $case['label'],
                    'summary' => $authoritativeResult->summary(),
                    'violations' => array_map(fn (Violation $v) => $v->toArray(), $authoritativeResult->violations),
                ];
                if ($fixResult !== null) {
                    $entry['fix'] = $fixResult->summary();
                    $entry['fixes_applied'] = array_map(fn (AppliedFix $f) => $f->toArray(), $fixResult->appliedFixes);
                    $entry['lint_before_fix'] = $result->summary();
                }
                $jsonOutput[] = $entry;
            } else {
                $this->renderResult($case['label'], $authoritativeResult, $isBatch);
                if ($fixResult !== null && $reLintResult !== null) {
                    $this->renderFixSummary($fixResult, $result, $reLintResult);
                }
            }
        }

        if ($isJson) {
            $this->line(json_encode([
                'mode' => $isBatch ? 'batch' : 'single',
                'cases' => $jsonOutput,
                'aggregated' => $aggregated,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return $worstExit;
        }

        if ($isBatch) {
            $this->renderAggregated($aggregated, count($cases));
        }

        return $worstExit;
    }

    /**
     * @return array<int, array{label: string, plan: array, vertical: ?string}>|null
     */
    private function buildCases(): ?array
    {
        if ($file = $this->option('file')) {
            return $this->casesFromFile((string) $file);
        }
        if ($id = $this->option('id')) {
            return $this->casesFromAssignedPlanId((int) $id);
        }
        if ($this->option('client')) {
            return $this->casesFromClient(
                (int) $this->option('client'),
                (string) $this->option('plan-type'),
            );
        }
        if ($tier = $this->option('batch')) {
            return $this->casesFromBatch((string) $tier, (int) $this->option('limit'));
        }

        $this->error('Debes especificar uno de: --file, --id, --client+--plan-type, o --batch.');
        return null;
    }

    private function casesFromFile(string $file): ?array
    {
        if (! is_file($file)) {
            $this->error("Archivo no encontrado: $file");
            return null;
        }
        try {
            $plan = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->error("JSON inválido en $file: " . $e->getMessage());
            return null;
        }
        return [[
            'label' => "file:$file",
            'plan' => is_array($plan) ? $plan : [],
            'vertical' => $this->option('vertical') ?? ($plan['plan_type'] ?? null),
            'file_path' => $file,
        ]];
    }

    private function casesFromAssignedPlanId(int $id): ?array
    {
        $row = AssignedPlan::query()->find($id);
        if ($row === null) {
            $this->error("assigned_plans.id=$id no encontrado.");
            return null;
        }
        return [[
            'label' => "assigned_plan:$id (client=$row->client_id, type=$row->plan_type)",
            'plan' => is_array($row->content) ? $row->content : [],
            'vertical' => $this->option('vertical') ?? $row->plan_type,
        ]];
    }

    private function casesFromClient(int $clientId, string $planType): ?array
    {
        if ($planType === '') {
            $this->error('--client requiere también --plan-type.');
            return null;
        }
        $row = AssignedPlan::query()
            ->forClient($clientId)
            ->where('plan_type', $planType)
            ->active()
            ->orderByDesc('id')
            ->first();
        if ($row === null) {
            $this->error("No hay plan activo para client_id=$clientId tipo=$planType.");
            return null;
        }
        return [[
            'label' => "client:$clientId plan_type:$planType (assigned_plan:$row->id)",
            'plan' => is_array($row->content) ? $row->content : [],
            'vertical' => $this->option('vertical') ?? $planType,
        ]];
    }

    private function casesFromBatch(string $tier, int $limit): array
    {
        $rows = AssignedPlan::query()
            ->whereHas('client', fn ($q) => $q->where('plan', $tier))
            ->active()
            ->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get();

        $cases = [];
        foreach ($rows as $row) {
            $cases[] = [
                'label' => "tier:$tier assigned_plan:$row->id (client=$row->client_id, type=$row->plan_type)",
                'plan' => is_array($row->content) ? $row->content : [],
                'vertical' => $row->plan_type,
            ];
        }
        return $cases;
    }

    private function renderResult(string $label, LintResult $result, bool $isBatch): void
    {
        $this->newLine();
        $this->info("═══ Lint: $label ═══");
        $sum = $result->summary();
        $this->line(sprintf(
            'rules evaluadas: %d · skipped: %d · errors: %d · warnings: %d · infos: %d · %.1f ms · %s',
            $sum['rules_evaluated'],
            $sum['rules_skipped'],
            $sum['errors'],
            $sum['warnings'],
            $sum['infos'],
            $sum['duration_ms'],
            $sum['passes'] ? '✓ PASA' : '✗ FALLA',
        ));

        if (count($result->violations) === 0) {
            $this->info('Sin violations. Plan limpio.');
            return;
        }

        $maxShow = $isBatch ? 5 : 50;
        $shown = 0;
        foreach ($result->violations as $v) {
            if ($shown >= $maxShow) {
                $this->line(sprintf('… y %d violations más (use --json para verlas todas)', count($result->violations) - $shown));
                break;
            }
            $icon = match ($v->severity) {
                'error' => '✗',
                'warning' => '⚠',
                default => 'ℹ',
            };
            $this->line(sprintf('  %s [%s] %s', $icon, $v->ruleCode, $v->message));
            $this->line(sprintf('     path: %s', $v->jsonPath));
            if ($v->fixHint !== null && $v->fixHint !== '') {
                $this->line(sprintf('     fix:  %s', $v->fixHint));
            }
            $shown++;
        }
    }

    private function renderFixSummary(FixResult $fixResult, LintResult $originalLint, LintResult $afterLint): void
    {
        $this->newLine();
        $this->info('═══ AutoFix ═══');
        $sum = $fixResult->summary();
        $beforeErrors = count($originalLint->errors());
        $beforeWarnings = count($originalLint->warnings());
        $afterErrors = count($afterLint->errors());
        $afterWarnings = count($afterLint->warnings());

        $this->line(sprintf(
            'fixes aplicados: %d · skipped (no auto-fixable): %d · failed: %d · %.1f ms',
            $sum['applied'],
            $sum['skipped_not_auto_fixable'],
            $sum['failed'],
            $sum['duration_ms'],
        ));
        $this->line(sprintf(
            'antes:   %d errors + %d warnings',
            $beforeErrors,
            $beforeWarnings,
        ));
        $this->line(sprintf(
            'después: %d errors + %d warnings (Δ -%d errors, -%d warnings)',
            $afterErrors,
            $afterWarnings,
            $beforeErrors - $afterErrors,
            $beforeWarnings - $afterWarnings,
        ));

        if (count($fixResult->appliedFixes) === 0) {
            return;
        }
        foreach ($fixResult->appliedFixes as $fix) {
            $this->line(sprintf('  ✓ [%s] %s', $fix->ruleCode, $fix->summary));
        }
    }

    private function writeFixedFile(string $originalPath, array $fixedPlan): void
    {
        $fixedPath = preg_replace('/(\.json)$/i', '.fixed.json', $originalPath) ?? $originalPath . '.fixed.json';
        file_put_contents(
            $fixedPath,
            json_encode($fixedPlan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n",
        );
        $this->newLine();
        $this->info("✎ Plan corregido escrito en: $fixedPath");
    }

    private function renderAggregated(array $agg, int $totalCases): void
    {
        $this->newLine();
        $this->info('═══ Agregado batch ═══');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Planes evaluados', $totalCases],
                ['Pasan (sin errors)', $agg['passes']],
                ['Fallan (>=1 error)', $agg['fails']],
                ['Total errors', $agg['errors']],
                ['Total warnings', $agg['warnings']],
                ['Total infos', $agg['infos']],
                ['Rules ejecutadas', $agg['evaluated']],
            ],
        );
    }
}
