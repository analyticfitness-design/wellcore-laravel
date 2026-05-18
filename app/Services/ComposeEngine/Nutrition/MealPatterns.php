<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Nutrition;

/**
 * Catálogo de patrones nutricionales por meal slot.
 *
 * Reglas LATAM:
 *   - Omnívoros (default): priorizar proteína animal en almuerzo/cena.
 *     Proteína vegetal solo si el cliente es vegano o vegetariano.
 *   - Desayuno LATAM clásico: huevos, arepa, queso bajo, fruta · O whey/avena/banano.
 *   - Almuerzo LATAM: pollo/pescado/carne + arroz/papa/yuca + ensalada + 1 cda aceite.
 *   - Cena LATAM: pescado/pollo + verdura + carbo pequeño (papa cocida, quinoa).
 *   - Pre-entreno: sin grasas (retrasan digestión 60-90 min antes del entreno).
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
     *   prefer_animal_protein: bool,
     * }
     */
    public static function forSlot(string $slotName): array
    {
        $name = mb_strtolower($slotName, 'UTF-8');
        return match (true) {
            str_contains($name, 'desayuno') => [
                'protein_categories' => ['proteina_animal', 'proteina_lactea', 'proteina_suplemento'],
                'carb_categories' => ['carbohidrato_grano', 'carbohidrato_latam'], // avena, arepa, pan integral
                'include_fat' => true,
                'include_vegetable' => false,
                'include_fruit' => true,
                'prefer_animal_protein' => true,
            ],
            str_contains($name, 'snack am'), str_contains($name, 'media manana') => [
                'protein_categories' => ['proteina_lactea', 'proteina_animal', 'proteina_suplemento'],
                'carb_categories' => ['fruta'],
                'include_fat' => false, // snack ligero — sin nueces obligatorias
                'include_vegetable' => false,
                'include_fruit' => false,
                'prefer_animal_protein' => true,
            ],
            str_contains($name, 'almuerzo') => [
                // Omnívoros: solo animal. Vegano lo activamos vía dietary_restriction.
                'protein_categories' => ['proteina_animal'],
                'carb_categories' => ['carbohidrato_grano', 'carbohidrato_tuberculo', 'carbohidrato_latam'],
                'include_fat' => true,
                'include_vegetable' => true,
                'include_fruit' => false,
                'prefer_animal_protein' => true,
            ],
            str_contains($name, 'merienda') => [
                'protein_categories' => ['proteina_lactea', 'proteina_suplemento', 'proteina_animal'],
                'carb_categories' => ['fruta', 'carbohidrato_grano'],
                'include_fat' => false,
                'include_vegetable' => false,
                'include_fruit' => false,
                'prefer_animal_protein' => true,
            ],
            str_contains($name, 'pre-entreno'), str_contains($name, 'pre entreno') => [
                'protein_categories' => ['proteina_suplemento', 'proteina_animal'],
                'carb_categories' => ['fruta', 'carbohidrato_grano', 'carbohidrato_latam'],
                'include_fat' => false, // sin grasas — retrasan digestión
                'include_vegetable' => false,
                'include_fruit' => false,
                'prefer_animal_protein' => true,
            ],
            str_contains($name, 'cena') => [
                'protein_categories' => ['proteina_animal'],
                'carb_categories' => ['carbohidrato_tuberculo', 'carbohidrato_grano'],
                'include_fat' => true,
                'include_vegetable' => true,
                'include_fruit' => false,
                'prefer_animal_protein' => true,
            ],
            str_contains($name, 'post-entreno'), str_contains($name, 'post entreno') => [
                'protein_categories' => ['proteina_suplemento', 'proteina_animal'],
                'carb_categories' => ['fruta', 'carbohidrato_grano'],
                'include_fat' => false,
                'include_vegetable' => false,
                'include_fruit' => false,
                'prefer_animal_protein' => true,
            ],
            default => [
                'protein_categories' => ['proteina_animal', 'proteina_lactea'],
                'carb_categories' => ['carbohidrato_grano'],
                'include_fat' => true,
                'include_vegetable' => false,
                'include_fruit' => false,
                'prefer_animal_protein' => true,
            ],
        };
    }
}
