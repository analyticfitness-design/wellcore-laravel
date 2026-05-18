<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Contracts;

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\Violation;

/**
 * Contrato común para todos los validators del LintEngine.
 *
 * Cada validator recibe un LintContext (plan + rule + check_definition)
 * y retorna un array de Violation[]. Array vacío = check pasa.
 *
 * Los validators deben ser puros (sin efectos secundarios) y deterministas.
 * El único validator con I/O es ExternalHeadValidator (HTTP HEAD a URLs).
 */
interface Validator
{
    /**
     * Nombre canónico del validator usado en check_definition_json.validator.
     * Debe ser único en el ValidatorRegistry.
     */
    public function name(): string;

    /**
     * Ejecuta el check sobre el contexto.
     *
     * @return Violation[]
     */
    public function check(LintContext $context): array;
}
