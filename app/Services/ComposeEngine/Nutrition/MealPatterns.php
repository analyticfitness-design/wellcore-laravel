<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Nutrition;

/**
 * Catálogo de patrones nutricionales por meal slot.
 *
 * Cada pattern define:
 *   - protein_categories: qué tipo de proteína (animal magra para almuerzo,
 *     láctea/suplemento para snack, magra simple para pre-entreno)
 *   - carb_categories: qué tipo de carbo (grano integral en almuerzo, fruta
 *     simple en desayuno/pre-entreno, ninguno en algunos snacks)
 *   - include_fat: si la comida debería incluir grasa adicional
 *   - include_vegetable: si va vegetal de acompañamiento
 *   - include_fruit: si va fruta
 *
 * Reglas nutricionales aplicadas:
 *   - Pre-entreno: SIN grasas (retrasan digestión 60-90 min antes del entreno).
 *     Carbos simples (fruta) + proteína magra (whey o pollo).
 *   - Snack AM: liviano. Lácteo o suplemento + nuez o fruta.
 *   - Desayuno: completo con fruta para vitaminas matutinas.
 *   - Almuerzo/Cena: comidas principales con vegetal de acompañamiento.
 */
final class MealPatterns
{
    /**
     * @return array{
     *   protein_categories: string[],
     *   carb_categories: string[],
     *   include_fat: bool,
     *   include_vegetable: bool,
     *   include_fruit: bool,
     * }
     */
    public static function forSlot(string $slotName): array
    {
        return match ($slotName) {
            'Desayuno' => [
                'protein_categories' => ['proteina_animal', 'proteina_lactea', 'proteina_suplemento'],
                'carb_categories' => ['carbohidrato_grano'], // avena, granola
                'include_fat' => true,
                'include_vegetable' => false,
                'include_fruit' => true,  // ← fruta de mañana
            ],
            'Snack AM' => [
                'protein_categories' => ['proteina_lactea', 'proteina_suplemento', 'proteina_vegetal'],
                'carb_categories' => ['fruta'],  // fruta como carbo (banano + yogur clásico)
                'include_fat' => true,           // pequeña porción de nueces ok
                'include_vegetable' => false,
                'include_fruit' => false,         // fruta ya cubierta via carb_categories
            ],
            'Almuerzo' => [
                'protein_categories' => ['proteina_animal', 'proteina_vegetal'],
                'carb_categories' => ['carbohidrato_grano', 'carbohidrato_tuberculo', 'carbohidrato_latam'],
                'include_fat' => true,
                'include_vegetable' => true,
                'include_fruit' => false,
            ],
            'Pre-entreno' => [
                'protein_categories' => ['proteina_suplemento', 'proteina_animal'], // magras
                'carb_categories' => ['fruta', 'carbohidrato_grano'], // carbos simples/rápidos
                'include_fat' => false,  // ← SIN grasas, retrasan digestión
                'include_vegetable' => false,
                'include_fruit' => false, // cubierta en carbs si aplica
            ],
            'Cena' => [
                'protein_categories' => ['proteina_animal', 'proteina_vegetal'],
                'carb_categories' => ['carbohidrato_tuberculo', 'carbohidrato_grano'],
                'include_fat' => true,
                'include_vegetable' => true,  // ← cena con verde
                'include_fruit' => false,
            ],
            default => [
                'protein_categories' => ['proteina_animal', 'proteina_lactea'],
                'carb_categories' => ['carbohidrato_grano'],
                'include_fat' => true,
                'include_vegetable' => false,
                'include_fruit' => false,
            ],
        };
    }
}
