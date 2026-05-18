<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Verifica que la proteína total de las comidas coincida con el target macro.
 *
 * Solo aplica a plan_type=nutricion.
 *
 * Algoritmo:
 *   1. Lee `macros.proteina_g` del plan (target diario).
 *   2. Suma proteína de cada `comidas[i].macros.proteina` (la opción mediana implícita).
 *   3. Si el delta % entre suma y target excede tolerance (default 10%), warning.
 *
 * Razón: el motor v2 calcula macros target con Mifflin-St Jeor y luego distribuye
 * por comida. Un drift entre target y suma indica bug en MealsBuilder o macros mal
 * calculados. Es un catch de regresión silenciosa.
 *
 * Usado por: heur_proteina_daily_mismatch.
 */
final class ProteinaDailyTargetValidator extends BaseValidator
{
    public function name(): string
    {
        return 'proteina_daily_target';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;

        if (($plan['plan_type'] ?? null) !== 'nutricion') {
            return [];
        }

        $tolerancePct = (float) ($ctx->checkDefinition['tolerance_pct'] ?? 10.0);

        $target = $plan['macros']['proteina_g'] ?? null;
        if (! is_numeric($target) || $target <= 0) {
            return [];
        }

        $comidas = $plan['comidas'] ?? [];
        if (! is_array($comidas) || $comidas === []) {
            return [];
        }

        // Suma proteína de cada comida (el "target" del macros de cada comida)
        $sumaComidas = 0;
        foreach ($comidas as $c) {
            $p = $c['macros']['proteina'] ?? 0;
            if (is_numeric($p)) {
                $sumaComidas += (float) $p;
            }
        }

        if ($sumaComidas == 0) {
            return [];
        }

        $diff = abs($sumaComidas - $target);
        $diffPct = ($diff / $target) * 100;

        if ($diffPct > $tolerancePct) {
            return [$this->makeViolation(
                $ctx,
                '$.comidas[*].macros.proteina',
                sprintf(
                    'Mismatch proteína: macros.proteina_g target=%dg, suma de comidas=%dg (diff %.1f%% > tolerance %.1f%%). Indica bug en MealsBuilder o macros mal distribuidos.',
                    (int) $target, (int) $sumaComidas, $diffPct, $tolerancePct,
                ),
                [
                    'target_proteina_g' => $target,
                    'suma_comidas_proteina' => $sumaComidas,
                    'diff_pct' => round($diffPct, 1),
                    'tolerance_pct' => $tolerancePct,
                ],
            )];
        }

        return [];
    }
}
