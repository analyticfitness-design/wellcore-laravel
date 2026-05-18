<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Data;

/**
 * Snapshot inmutable de una fila de wellcore_kb.lint_rules.
 *
 * Se construye desde el modelo Eloquent LintRule pero se desacopla
 * para que los validators no dependan de Eloquent.
 */
final readonly class LintRuleMeta
{
    public function __construct(
        public string $code,
        public ?string $vertical,
        public string $severity,
        public string $description,
        public string $checkType,
        public string $fixHintTemplate,
        public bool $autoFixAvailable,
    ) {
    }
}
