<?php

declare(strict_types=1);

namespace App\Services\LintEngine;

use App\Models\Kb\LintRule;
use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintResult;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\Data\Violation;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Orquestador del linter pre-INSERT del motor v2 (Stage 4 VALIDATE).
 *
 * Flujo:
 *   1. Carga rules de wellcore_kb.lint_rules (filtradas por enabled + vertical).
 *   2. Para cada rule: determina qué validator usar según check_type + check_definition.
 *   3. Ejecuta el validator con LintContext (plan + rule + definition).
 *   4. Agrega violations y retorna LintResult.
 *
 * El método principal `lint()` es puro respecto al plan (no muta el JSON).
 * El motor v2 lo invoca antes de PERSIST. Si LintResult::passes() === false,
 * el plan NO se inserta.
 */
final class LintEngine
{
    public function __construct(
        private readonly ValidatorRegistry $registry,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Ejecuta el linter contra un plan decodificado.
     *
     * @param array $plan El JSON del plan (assigned_plans.content decodificado)
     * @param string|null $vertical El plan_type ("entrenamiento"|"nutricion"|...) o null para todas
     */
    public function lint(array $plan, ?string $vertical = null): LintResult
    {
        $start = microtime(true);
        $rules = $this->loadRules($vertical);

        $violations = [];
        $evaluated = 0;
        $skipped = 0;

        foreach ($rules as $rule) {
            try {
                $meta = $this->toMeta($rule);
                $checkDef = $rule->check_definition_json ?? [];
                $validatorName = $this->resolveValidatorName($meta, $checkDef);

                if ($validatorName === null) {
                    $skipped++;
                    $this->logger->warning("LintEngine: rule '{$meta->code}' sin validator resolvible (check_type={$meta->checkType}).");
                    continue;
                }

                if (! $this->registry->has($validatorName)) {
                    $skipped++;
                    $this->logger->warning("LintEngine: validator '$validatorName' no registrado (rule {$meta->code}).");
                    continue;
                }

                $validator = $this->registry->get($validatorName);
                $context = new LintContext(
                    plan: $plan,
                    rule: $meta,
                    checkDefinition: is_array($checkDef) ? $checkDef : [],
                    vertical: $vertical,
                );

                $ruleViolations = $validator->check($context);
                foreach ($ruleViolations as $v) {
                    $violations[] = $v;
                }
                $evaluated++;
            } catch (Throwable $e) {
                $skipped++;
                $this->logger->error(
                    "LintEngine: rule '{$rule->code}' falló en ejecución: " . $e->getMessage(),
                    ['exception' => $e]
                );
            }
        }

        $duration = (microtime(true) - $start) * 1000;
        return new LintResult($violations, $evaluated, $skipped, $duration);
    }

    /**
     * Aplica el linter a múltiples planes (batch). Útil para retroactiva.
     *
     * @param iterable<int, array{plan: array, vertical: ?string}> $items
     * @return array<int, LintResult>
     */
    public function lintBatch(iterable $items): array
    {
        $results = [];
        foreach ($items as $i => $item) {
            $results[$i] = $this->lint($item['plan'], $item['vertical'] ?? null);
        }
        return $results;
    }

    /**
     * @return Collection<int, LintRule>
     */
    private function loadRules(?string $vertical): Collection
    {
        return LintRule::query()
            ->enabled()
            ->forVertical($vertical)
            ->orderBy('check_type')
            ->orderBy('id')
            ->get();
    }

    /**
     * Mapea check_type + check_definition al nombre del validator a usar.
     *
     * Algunas reglas tienen `check_definition.validator` explícito; otras lo
     * infieren del check_type (heuristic con `rule` key) o usan defaults.
     */
    private function resolveValidatorName(LintRuleMeta $meta, mixed $checkDef): ?string
    {
        if (! is_array($checkDef)) {
            return null;
        }

        // 1. Explícito: check_definition.validator
        if (isset($checkDef['validator']) && is_string($checkDef['validator'])) {
            return $checkDef['validator'];
        }

        // 2. Heuristic con `rule` key — convención de los seeders
        if ($meta->checkType === 'heuristic' && isset($checkDef['rule']) && is_string($checkDef['rule'])) {
            return $checkDef['rule'];
        }

        // 3. Heuristic con `patterns` y `json_paths` → regex pattern validator
        if ($meta->checkType === 'heuristic' && isset($checkDef['patterns'], $checkDef['json_paths'])) {
            return 'regex_patterns_in_paths';
        }

        // 4. External head con expected_pattern → es pattern check (sin HTTP)
        if ($meta->checkType === 'external_head' && isset($checkDef['expected_pattern']) && ! isset($checkDef['method'])) {
            return 'url_matches_pattern';
        }

        // 5. External head con method (HEAD) → HTTP check real
        if ($meta->checkType === 'external_head') {
            return 'external_head';
        }

        // 6. SQL check: valor en allowed_values
        if ($meta->checkType === 'sql' && isset($checkDef['allowed_values'])) {
            return 'allowed_values';
        }

        return null;
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
