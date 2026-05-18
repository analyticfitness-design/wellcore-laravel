<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Verifica que el plan de nutrición incluya target explícito de hidratación.
 *
 * Razón fisiológica: la hidratación es macronutriente funcional ignorado en
 * planes mediocres. Mínimo 30-35 ml/kg/día (≈2-3L para adulto promedio).
 * Subhidratación crónica causa: fatiga, performance ↓, recovery ↓, headache,
 * confusión saciedad/sed (overeating).
 *
 * Detección:
 *   1. Solo aplica a plan_type=nutricion.
 *   2. Busca campo `hidratacion_ml_dia` en macros (preferido).
 *   3. O busca mención de "agua", "hidratacion", "litros" en tips/notas con
 *      número adyacente.
 *   4. Si no hay nada → warning.
 *   5. Si hay target pero <25 ml/kg/día (asumiendo peso del profile) → warning de subhidratación.
 *
 * Usado por: heur_hydration_target_missing.
 */
final class HydrationTargetValidator extends BaseValidator
{
    private const MIN_ML_PER_KG = 25; // Mínimo aceptable (target sano: 30-35)

    public function name(): string
    {
        return 'hydration_target';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;

        if (($plan['plan_type'] ?? null) !== 'nutricion') {
            return [];
        }

        $macros = $plan['macros'] ?? [];
        $explicitTarget = null;
        if (is_array($macros) && isset($macros['hidratacion_ml_dia']) && is_numeric($macros['hidratacion_ml_dia'])) {
            $explicitTarget = (int) $macros['hidratacion_ml_dia'];
        }

        if ($explicitTarget === null) {
            $explicitTarget = $this->scanTipsAndNotes($plan);
        }

        if ($explicitTarget === null) {
            return [$this->makeViolation(
                $ctx,
                '$.macros.hidratacion_ml_dia',
                'Plan de nutrición SIN target de hidratación. Agregar macros.hidratacion_ml_dia (recomendado 30-35 ml/kg/día) o mencionarlo en tips.',
                [
                    'recomendacion_ml_kg' => '30-35 ml/kg/día',
                    'ejemplo_adulto_70kg' => '~2100-2450 ml/día',
                ],
            )];
        }

        // Subhidratación: si el plan declara peso (en macros o root), verificamos ratio.
        // LintContext no tiene profile expuesto, pero los planes generados por el motor
        // suelen incluir macros.peso_kg o root.peso_kg para auditoría.
        $pesoKg = $plan['peso_kg']
            ?? $plan['macros']['peso_kg']
            ?? $plan['profile']['peso_kg']
            ?? $plan['profile']['weight_kg']
            ?? null;
        if (is_numeric($pesoKg) && $pesoKg > 0) {
            $ratioMlKg = $explicitTarget / (float) $pesoKg;
            if ($ratioMlKg < self::MIN_ML_PER_KG) {
                return [$this->makeViolation(
                    $ctx,
                    '$.macros.hidratacion_ml_dia',
                    sprintf(
                        'Target hidratación bajo: %d ml/día para %.1f kg = %.1f ml/kg/día (mínimo %d). Subir a 30-35 ml/kg.',
                        $explicitTarget, (float) $pesoKg, $ratioMlKg, self::MIN_ML_PER_KG,
                    ),
                    [
                        'actual_ml_dia' => $explicitTarget,
                        'peso_kg' => (float) $pesoKg,
                        'ratio_ml_kg_actual' => round($ratioMlKg, 1),
                        'ratio_ml_kg_minimo' => self::MIN_ML_PER_KG,
                        'objetivo_recomendado_ml_dia' => (int) round(((float) $pesoKg) * 32),
                    ],
                )];
            }
        }

        return [];
    }

    private function scanTipsAndNotes(array $plan): ?int
    {
        $haystack = [];
        foreach ((array) ($plan['tips'] ?? []) as $tip) {
            if (is_string($tip)) {
                $haystack[] = mb_strtolower($tip);
            }
        }
        if (! empty($plan['notas_coach'])) {
            $haystack[] = mb_strtolower((string) $plan['notas_coach']);
        }
        $text = implode(' ', $haystack);

        // Patrones: "2.5 litros de agua", "2500 ml", "3 L de agua", "8 vasos"
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(?:l|litro|litros|lt)\b/u', $text, $m)) {
            $litros = (float) str_replace(',', '.', $m[1]);
            return (int) round($litros * 1000);
        }
        if (preg_match('/(\d{3,5})\s*(?:ml|mililitros)\b/u', $text, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/(\d{1,2})\s*vasos\b/u', $text, $m)) {
            return (int) $m[1] * 250; // vaso estándar ≈250ml
        }

        return null;
    }
}
