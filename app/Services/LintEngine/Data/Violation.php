<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Data;

/**
 * Una violación detectada por el linter.
 *
 * Inmutable. Producida por cualquier Validator que detecte que el plan
 * no cumple una rule específica.
 */
final readonly class Violation
{
    /**
     * @param string $ruleCode  Código de la lint_rule (ej. "schema_train_missing_split")
     * @param string $severity  "error" | "warning" | "info"
     * @param string $jsonPath  Path JSON donde se detectó (ej. "$.semanas[2].fase")
     * @param string $message   Mensaje human-readable
     * @param string|null $fixHint  Sugerencia de fix (del fix_hint_template)
     * @param mixed $foundValue Valor encontrado (para debug; puede ser null/array/string/etc.)
     * @param bool $autoFixAvailable  Si true, el motor puede aplicar fix automático
     */
    public function __construct(
        public string $ruleCode,
        public string $severity,
        public string $jsonPath,
        public string $message,
        public ?string $fixHint = null,
        public mixed $foundValue = null,
        public bool $autoFixAvailable = false,
    ) {
    }

    public function toArray(): array
    {
        return [
            'rule_code' => $this->ruleCode,
            'severity' => $this->severity,
            'json_path' => $this->jsonPath,
            'message' => $this->message,
            'fix_hint' => $this->fixHint,
            'found_value' => $this->describeFoundValue(),
            'auto_fix_available' => $this->autoFixAvailable,
        ];
    }

    private function describeFoundValue(): string
    {
        return match (true) {
            is_null($this->foundValue) => 'null',
            is_string($this->foundValue) => '"' . mb_substr($this->foundValue, 0, 80) . '"',
            is_bool($this->foundValue) => $this->foundValue ? 'true' : 'false',
            is_array($this->foundValue) => 'array(' . count($this->foundValue) . ')',
            is_object($this->foundValue) => get_class($this->foundValue),
            default => (string) $this->foundValue,
        };
    }
}
