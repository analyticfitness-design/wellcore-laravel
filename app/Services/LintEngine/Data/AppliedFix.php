<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Data;

/**
 * Resultado de aplicar un AutoFix sobre el plan.
 *
 * Inmutable. Lleva tanto el plan modificado como metadata de qué cambió.
 */
final readonly class AppliedFix
{
    public function __construct(
        public string $ruleCode,
        public string $fixerName,
        public string $jsonPath,
        public mixed $before,
        public mixed $after,
        public string $summary,
        public array $fixedPlan,
    ) {
    }

    public function toArray(): array
    {
        return [
            'rule_code' => $this->ruleCode,
            'fixer_name' => $this->fixerName,
            'json_path' => $this->jsonPath,
            'before' => $this->describe($this->before),
            'after' => $this->describe($this->after),
            'summary' => $this->summary,
        ];
    }

    private function describe(mixed $value): string
    {
        if (is_null($value)) return 'null';
        if (is_string($value)) return '"' . mb_substr($value, 0, 120) . '"';
        if (is_array($value)) return 'array(' . count($value) . ')';
        if (is_scalar($value)) return (string) $value;
        return get_debug_type($value);
    }
}
