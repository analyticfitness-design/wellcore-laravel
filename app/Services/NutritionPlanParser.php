<?php

namespace App\Services;

class NutritionPlanParser
{
    /**
     * Extract array of meals from any supported nutrition plan JSON shape.
     * Supported formats:
     *   - comidas[]
     *   - plan_dia_entrenamiento.comidas
     *   - meals[]
     *   - dias[n].comidas
     *   - plan_semanal[n].comidas
     *
     * @param  array<mixed>  $plan
     * @return array<int, array<string, mixed>>
     */
    public static function extractMeals(array $plan): array
    {
        $diasComidas = null;
        if (isset($plan['dias']) && is_array($plan['dias'])) {
            foreach ($plan['dias'] as $dia) {
                if (! empty($dia['comidas'])) {
                    $diasComidas = $dia['comidas'];
                    break;
                }
            }
        }

        $planSemanalComidas = null;
        if (isset($plan['plan_semanal']) && is_array($plan['plan_semanal'])) {
            foreach ($plan['plan_semanal'] as $dia) {
                if (! empty($dia['comidas'])) {
                    $planSemanalComidas = $dia['comidas'];
                    break;
                }
            }
        }

        $raw = $plan['comidas']
            ?? $plan['plan_dia_entrenamiento']['comidas']
            ?? $plan['meals']
            ?? $diasComidas
            ?? $planSemanalComidas
            ?? [];

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_map([self::class, 'normalizeMeal'], $raw));
    }

    /**
     * Normalize a single meal to canonical shape.
     * Handles ES/EN key variants from multiple AI plan formats.
     *
     * @param  array<string, mixed>  $meal
     * @return array<string, mixed>
     */
    public static function normalizeMeal(array $meal): array
    {
        $macros = is_array($meal['macros'] ?? null) ? $meal['macros'] : [];

        return [
            'nombre'    => $meal['nombre'] ?? $meal['name'] ?? $meal['label'] ?? 'Comida',
            'calorias'  => (int) ($meal['calorias'] ?? $meal['calories'] ?? $meal['kcal'] ?? $meal['cal'] ?? 0),
            'alimentos' => $meal['alimentos'] ?? $meal['foods'] ?? $meal['items'] ?? $meal['opciones'] ?? [],
            'notas'     => $meal['notas'] ?? $meal['notes'] ?? null,
            'macros' => [
                'proteina'      => (int) ($macros['proteina_g'] ?? $macros['proteina'] ?? $macros['protein_g'] ?? $macros['protein'] ?? 0),
                'carbohidratos' => (int) ($macros['carbs_g'] ?? $macros['carbohidratos_g'] ?? $macros['carbohidratos'] ?? $macros['carbs'] ?? 0),
                'grasas'        => (int) ($macros['grasas_g'] ?? $macros['grasa_g'] ?? $macros['grasas'] ?? $macros['fat_g'] ?? $macros['fat'] ?? 0),
            ],
        ];
    }
}
