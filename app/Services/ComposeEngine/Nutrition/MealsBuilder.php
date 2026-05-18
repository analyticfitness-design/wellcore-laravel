<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Nutrition;

use App\Services\ComposeEngine\Nutrition\Data\MealSlot;

/**
 * Construye los slots de comida para un día.
 *
 * Reparto canónico WellCore — share suma 1.00 por count:
 *   3 comidas: Desayuno (30%) · Almuerzo (40%) · Cena (30%)
 *   4 comidas: Desayuno (25%) · Snack AM (10%) · Almuerzo (35%) · Cena (30%)
 *   5 comidas: Desayuno (20%) · Snack AM (10%) · Almuerzo (30%) · Pre-entreno (15%) · Cena (25%)
 *   6 comidas: Desayuno (20%) · Snack AM (10%) · Almuerzo (25%) · Merienda (10%) · Pre-entreno (15%) · Cena (20%)
 *
 * Si el coach pasó horarios explícitos vía preferences.meal_times, se respetan (mismo count que slots).
 */
final class MealsBuilder
{
    /** Reparto canónico por count. */
    private const SHARES = [
        3 => [
            ['name' => 'Desayuno',    'hora' => '07:00', 'share' => 0.30],
            ['name' => 'Almuerzo',    'hora' => '13:00', 'share' => 0.40],
            ['name' => 'Cena',        'hora' => '20:00', 'share' => 0.30],
        ],
        4 => [
            ['name' => 'Desayuno',    'hora' => '07:00', 'share' => 0.25],
            ['name' => 'Snack AM',    'hora' => '10:30', 'share' => 0.10],
            ['name' => 'Almuerzo',    'hora' => '13:00', 'share' => 0.35],
            ['name' => 'Cena',        'hora' => '20:00', 'share' => 0.30],
        ],
        5 => [
            ['name' => 'Desayuno',    'hora' => '07:00', 'share' => 0.20],
            ['name' => 'Snack AM',    'hora' => '10:30', 'share' => 0.10],
            ['name' => 'Almuerzo',    'hora' => '13:00', 'share' => 0.30],
            ['name' => 'Pre-entreno', 'hora' => '17:00', 'share' => 0.15],
            ['name' => 'Cena',        'hora' => '20:00', 'share' => 0.25],
        ],
        6 => [
            ['name' => 'Desayuno',    'hora' => '07:00', 'share' => 0.20],
            ['name' => 'Snack AM',    'hora' => '10:30', 'share' => 0.10],
            ['name' => 'Almuerzo',    'hora' => '13:00', 'share' => 0.25],
            ['name' => 'Merienda',    'hora' => '16:00', 'share' => 0.10],
            ['name' => 'Pre-entreno', 'hora' => '18:00', 'share' => 0.15],
            ['name' => 'Cena',        'hora' => '21:00', 'share' => 0.20],
        ],
    ];

    public const SUPPORTED_COUNTS = [3, 4, 5, 6];

    /**
     * @param array{objetivo_cal: int, macros: array{proteina_g: int, carbohidratos_g: int, grasas_g: int}} $macroPlan
     * @param string[]|null $customTimes opcional, mismo count que $mealsCount; override del hora canónico
     * @return MealSlot[]
     */
    public function build(array $macroPlan, int $mealsCount = 5, ?array $customTimes = null): array
    {
        if (! in_array($mealsCount, self::SUPPORTED_COUNTS, true)) {
            $supported = implode('|', self::SUPPORTED_COUNTS);
            throw new \RuntimeException("MealsBuilder soporta {$supported} comidas. Recibido: {$mealsCount}");
        }

        $shares = self::SHARES[$mealsCount];

        // Si el coach pasó horarios y matchea el count, los usamos.
        if (is_array($customTimes) && count($customTimes) === $mealsCount) {
            foreach ($shares as $i => &$slot) {
                $slot['hora'] = $customTimes[$i];
            }
            unset($slot);
        }

        $slots = [];
        foreach ($shares as $meal) {
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
