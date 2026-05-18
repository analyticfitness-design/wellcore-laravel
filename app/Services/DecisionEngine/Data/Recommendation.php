<?php

declare(strict_types=1);

namespace App\Services\DecisionEngine\Data;

/**
 * Una recomendación de metodología producida por una decision_rule.
 *
 * Inmutable. Lleva la metodología elegida, la confidence, y el rationale
 * para auditoría (por qué se eligió esta y no otra).
 */
final readonly class Recommendation
{
    public function __construct(
        public int $ruleId,
        public string $ruleName,
        public int $methodologyId,
        public string $methodologySlug,
        public string $methodologyName,
        public string $vertical,
        public float $confidence,
        public string $rationale,
        public array $matchedConditions,
    ) {
    }

    public function toArray(): array
    {
        return [
            'rule_id' => $this->ruleId,
            'rule_name' => $this->ruleName,
            'methodology_id' => $this->methodologyId,
            'methodology_slug' => $this->methodologySlug,
            'methodology_name' => $this->methodologyName,
            'vertical' => $this->vertical,
            'confidence' => $this->confidence,
            'rationale' => $this->rationale,
            'matched_conditions' => $this->matchedConditions,
        ];
    }
}
