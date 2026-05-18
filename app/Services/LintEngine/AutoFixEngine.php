<?php

declare(strict_types=1);

namespace App\Services\LintEngine;

use App\Models\Kb\LintRule;
use App\Services\LintEngine\Data\AppliedFix;
use App\Services\LintEngine\Data\FixContext;
use App\Services\LintEngine\Data\FixResult;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\Data\Violation;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Orquestador de auto-fixes del LintEngine.
 *
 * Flujo:
 *   1. Recibe un plan + violations detectadas por LintEngine.
 *   2. Para cada violation con autoFixAvailable=true:
 *      - Lee rule.check_definition.auto_fix.type
 *      - Resuelve el fixer correspondiente
 *      - Ejecuta apply() pasando un FixContext
 *      - Acumula el plan modificado
 *   3. Retorna FixResult con plan final + lista de cambios aplicados.
 *
 * Re-lint NO se hace aquí — eso lo encadena el llamador si quiere validar
 * que los fixes resolvieron las violations correctamente.
 */
final class AutoFixEngine
{
    public function __construct(
        private readonly FixerRegistry $fixers,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Aplica auto-fixes sobre un plan en base a las violations detectadas.
     *
     * @param array $plan El plan original
     * @param Violation[] $violations Violations detectadas previamente por LintEngine
     */
    public function applyAll(array $plan, array $violations): FixResult
    {
        $start = microtime(true);
        $current = $plan;
        $applied = [];
        $skipped = 0;
        $failed = 0;

        // Indexamos las rules por code para evitar N queries
        $codes = array_unique(array_map(fn (Violation $v) => $v->ruleCode, $violations));
        $rulesByCode = LintRule::query()->whereIn('code', $codes)->get()->keyBy('code');

        foreach ($violations as $violation) {
            if (! $violation->autoFixAvailable) {
                $skipped++;
                continue;
            }

            $rule = $rulesByCode->get($violation->ruleCode);
            if ($rule === null) {
                $this->logger->warning("AutoFixEngine: rule '{$violation->ruleCode}' no encontrada en DB.");
                $skipped++;
                continue;
            }

            $checkDef = is_array($rule->check_definition_json) ? $rule->check_definition_json : [];
            $autoFixDef = $checkDef['auto_fix'] ?? null;
            if (! is_array($autoFixDef) || ! isset($autoFixDef['type'])) {
                $skipped++;
                continue;
            }

            $fixerName = (string) $autoFixDef['type'];
            if (! $this->fixers->has($fixerName)) {
                $this->logger->warning("AutoFixEngine: fixer '$fixerName' no registrado (rule {$violation->ruleCode}).");
                $skipped++;
                continue;
            }

            try {
                $fixer = $this->fixers->get($fixerName);
                $context = new FixContext(
                    plan: $current,
                    violation: $violation,
                    rule: $this->toMeta($rule),
                    autoFixDefinition: $autoFixDef,
                );

                $result = $fixer->apply($context);
                if ($result === null) {
                    $skipped++;
                    continue;
                }

                $current = $result->fixedPlan;
                $applied[] = $result;
            } catch (Throwable $e) {
                $failed++;
                $this->logger->error(
                    "AutoFixEngine: fixer '$fixerName' falló (rule {$violation->ruleCode}): " . $e->getMessage(),
                    ['exception' => $e],
                );
            }
        }

        $duration = (microtime(true) - $start) * 1000;
        return new FixResult(
            fixedPlan: $current,
            appliedFixes: $applied,
            skipped: $skipped,
            failed: $failed,
            remainingViolations: [], // se llena después del re-lint si el caller lo hace
            durationMs: $duration,
        );
    }

    private function toMeta(LintRule $rule): LintRuleMeta
    {
        return new LintRuleMeta(
            code: (string) $rule->code,
            vertical: $rule->vertical,
            severity: (string) $rule->severity,
            description: (string) $rule->description,
            checkType: (string) $rule->check_type,
            fixHintTemplate: (string) $rule->fix_hint_template,
            autoFixAvailable: (bool) $rule->auto_fix_available,
        );
    }
}
