<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Data;

/**
 * Contexto inmutable que se pasa a cada Validator.
 *
 * Contiene:
 *  - $plan: el JSON decodificado del plan a evaluar (array asociativo)
 *  - $rule: metadata de la rule que se está ejecutando
 *  - $checkDefinition: el contenido decodificado de lint_rules.check_definition_json
 *  - $vertical: el plan_type del plan (entrenamiento/nutricion/etc.)
 *
 * El validator NO debe mutar el plan — es read-only conceptualmente.
 */
final readonly class LintContext
{
    public function __construct(
        public array $plan,
        public LintRuleMeta $rule,
        public array $checkDefinition,
        public ?string $vertical = null,
    ) {
    }

    /**
     * Helper para resolver el fix_hint_template con placeholders {var}.
     */
    public function renderFixHint(array $placeholders = []): string
    {
        $hint = $this->rule->fixHintTemplate;
        foreach ($placeholders as $key => $value) {
            $hint = str_replace('{' . $key . '}', (string) $value, $hint);
        }
        return $hint;
    }
}
