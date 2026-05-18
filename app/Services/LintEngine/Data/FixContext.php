<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Data;

/**
 * Contexto inmutable que recibe cada AutoFixer.
 *
 * Contiene:
 *  - $plan: snapshot actual del plan (puede haber recibido fixes previos)
 *  - $violation: la violation que se intenta arreglar
 *  - $rule: metadata de la rule (incluye auto_fix definition completo)
 *  - $autoFixDefinition: el sub-objeto check_definition.auto_fix
 */
final readonly class FixContext
{
    public function __construct(
        public array $plan,
        public Violation $violation,
        public LintRuleMeta $rule,
        public array $autoFixDefinition,
    ) {
    }
}
