<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Nutrition;

use App\Services\ComposeEngine\Nutrition\Data\MealSlot;

/**
 * Construye los slots de comida para un día.
 *
 * Default 5 comidas (estilo WellCore):
 *   Desayuno (20%), Snack AM (10%), Almuerzo (30%), Pre-entreno (15%), Cena (25%)
 *
 * Variantes futuras: 3 comidas (ayuno intermitente), 4 comidas, 6 comidas.
 * Por ahora Sprint 7 solo soporta el 5-meals.
 */
final class MealsBuilder
{
    /** Reparto canónico WellCore (suma 1.00). */
    private const FIVE_MEALS_SHARE = [
        ['name' => 'Desayuno',     'hora' => '07:00', 'share' => 0.20],
        ['name' => 'Snack AM',     'hora' => '10:30', 'share' => 0.10],
        ['name' => 'Almuerzo',     'hora' => '13:00', 'share' => 0.30],
        ['name' => 'Pre-entreno',  'hora' => '17:00', 'share' => 0.15],
        ['name' => 'Cena',         'hora' => '20:00', 'share' => 0.25],
    ];

    /**
     * @param array{objetivo_cal: int, macros: array{proteina_g: int, carbohidratos_g: int, grasas_g: int}} $macroPlan
     * @return MealSlot[]
     */
    public function build(array $macroPlan, int $mealsCount = 5): array
    {
        if ($mealsCount !== 5) {
            throw new \RuntimeException("MealsBuilder Sprint 7 solo soporta 5 comidas. Recibido: $mealsCount");
        }

        $slots = [];
        foreach (self::FIVE_MEALS_SHARE as $meal) {
            $slots[] = new MealSlot(
                name: $meal['name'],
                horaSugerida: $meal['hora'],
                kcalShare: $meal['share'],
                targetKcal: (int) round($macroPlan['objetivo_cal'] * $meal['share']),
                targetProteinaG: (int) round($macroPlan['macros']['proteina_g'] * $meal['share']),
                targetCarbosG: (int) round($macroPlan['macros']['carbohidratos_g'] * $meal['share']),
                targetGrasasG: (int) round($macroPlan['macros']['grasas_g'] * $meal['share']),
            );
        }

        return $slots;
    }
}
