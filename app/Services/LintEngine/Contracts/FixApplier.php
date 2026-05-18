<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Contracts;

use App\Services\LintEngine\Data\AppliedFix;
use App\Services\LintEngine\Data\FixContext;

/**
 * Contrato común para los AutoFixers del LintEngine.
 *
 * Cada fixer toma un FixContext (plan + violation + auto_fix definition)
 * y retorna AppliedFix|null. null significa "no pude aplicar el fix".
 *
 * Los fixers DEBEN ser determinísticos:
 *   - Mismo input → mismo output
 *   - No tienen estado entre invocaciones
 *   - Retornan una COPIA modificada del plan (no mutan el original)
 */
interface FixApplier
{
    /**
     * Nombre canónico del fixer usado en check_definition.auto_fix.type.
     */
    public function name(): string;

    /**
     * Aplica el fix sobre el contexto. Retorna AppliedFix con el plan modificado,
     * o null si el fix no era aplicable.
     */
    public function apply(FixContext $context): ?AppliedFix;
}
