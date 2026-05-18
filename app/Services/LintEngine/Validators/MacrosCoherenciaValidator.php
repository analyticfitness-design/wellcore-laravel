<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Verifica que macros.kcal sea coherente con macros (proteina*4) + (carbs*4) + (grasa*9).
 *
 * Tolerancia default: 5% (configurable via check_definition.tolerance_pct).
 *
 * Razón: drift de aritmética entre el output del MacroCalculator (Mifflin-St Jeor)
 * y la distribución por gramos es bug silencioso — el cliente ve "2000 kcal" pero
 * los macros suman 1700 o 2300. Catch de regresión.
 *
 * Solo aplica a plan_type=nutricion.
 * Skip si falta cualquier macro (lo cubre otro schema validator).
 *
 * Factores Atwater (rounded):
 *   - Proteína: 4 kcal/g
 *   - Carbohidratos: 4 kcal/g
 *   - Grasa: 9 kcal/g
 *   - Alcohol: 7 kcal/g (no contabilizado en planes nuestros)
 *
 * Usado por: heur_macros_coherencia.
 */
final class MacrosCoherenciaValidator extends BaseValidator
{
    private const KCAL_PROTEINA = 4;
    private const KCAL_CARBS = 4;
    private const KCAL_GRASA = 9;

    public function name(): string
    {
        return 'macros_coherencia';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;

        if (($plan['plan_type'] ?? null) !== 'nutricion') {
            return [];
        }

        $macros = $plan['macros'] ?? [];
        if (! is_array($macros)) {
            return [];
        }

        $kcalDeclarada = $macros['kcal'] ?? null;
        $proteinaG = $macros['proteina_g'] ?? null;
        $carbsG = $macros['carbohidratos_g'] ?? $macros['carbs_g'] ?? null;
        $grasaG = $macros['grasa_g'] ?? $macros['grasas_g'] ?? null;

        // Si falta cualquier campo, skip (schema validator se encarga)
        if (! is_numeric($kcalDeclarada) || $kcalDeclarada <= 0) {
            return [];
        }
        if (! is_numeric($proteinaG) || ! is_numeric($carbsG) || ! is_numeric($grasaG)) {
            return [];
        }

        $kcalCalculada = ($proteinaG * self::KCAL_PROTEINA)
            + ($carbsG * self::KCAL_CARBS)
            + ($grasaG * self::KCAL_GRASA);

        $tolerancePct = (float) ($ctx->checkDefinition['tolerance_pct'] ?? 5.0);
        $diff = abs($kcalCalculada - $kcalDeclarada);
        $diffPct = ($diff / (float) $kcalDeclarada) * 100;

        if ($diffPct > $tolerancePct) {
            return [$this->makeViolation(
                $ctx,
                '$.macros',
                sprintf(
                    'Macros incoherentes: kcal declarada=%d, kcal calculada (4P + 4C + 9G) = %d (P=%dg, C=%dg, G=%dg). Diff %.1f%% supera tolerance %.1f%%.',
                    (int) $kcalDeclarada,
                    (int) $kcalCalculada,
                    (int) $proteinaG,
                    (int) $carbsG,
                    (int) $grasaG,
                    $diffPct,
                    $tolerancePct,
                ),
                [
                    'kcal_declarada' => (float) $kcalDeclarada,
                    'kcal_calculada' => round($kcalCalculada, 1),
                    'proteina_g' => (float) $proteinaG,
                    'carbohidratos_g' => (float) $carbsG,
                    'grasa_g' => (float) $grasaG,
                    'diff_pct' => round($diffPct, 2),
                    'tolerance_pct' => $tolerancePct,
                    'factores' => [
                        'kcal_por_g_proteina' => self::KCAL_PROTEINA,
                        'kcal_por_g_carbs' => self::KCAL_CARBS,
                        'kcal_por_g_grasa' => self::KCAL_GRASA,
                    ],
                ],
            )];
        }

        return [];
    }
}
