<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Nutrition;

use App\Services\DecisionEngine\Data\ClientProfile;

/**
 * Calcula BMR (Mifflin-St Jeor), TDEE/GET y macros target según goal.
 *
 * Defaults conservadores cuando faltan datos del profile:
 *  - sin weight_kg: 70 kg
 *  - sin height_cm: 170 cm
 *  - sin age: 30
 *  - sin gender: M (femenino restaría ~150 kcal)
 *  - sin level: 'intermedio' (activity factor 1.55)
 *
 * Ajuste por goal:
 *  - perdida_grasa: TDEE − 400 kcal (déficit moderado)
 *  - recomposicion: TDEE − 150 kcal
 *  - mantenimiento: TDEE
 *  - hipertrofia: TDEE + 250 kcal (superávit lean bulk)
 *
 * Macros:
 *  - proteína: 1.8-2.4 g/kg (escalonado: 2.4 en déficit, 2.0 manteniendo, 1.8 mantenimiento)
 *  - grasas: 0.8-1.0 g/kg (28-33% kcal típico)
 *  - carbos: resto de kcal disponibles
 *
 * Pure service — no DB, no side effects. 100% testeable.
 */
final class MacroCalculator
{
    private const DEFAULT_WEIGHT_KG = 70.0;
    private const DEFAULT_HEIGHT_CM = 170.0;
    private const DEFAULT_AGE = 30;

    /** Activity factor por nivel. */
    private const ACTIVITY_FACTOR = [
        'principiante' => 1.45,  // sedentario+ → ligero
        'intermedio' => 1.55,    // moderado (3-5 días/sem)
        'avanzado' => 1.725,     // alto (6-7 días/sem)
    ];

    /**
     * Retorna ['bmr' => int, 'tdee' => int, 'objetivo_cal' => int,
     *          'macros' => ['proteina_g' => int, 'carbohidratos_g' => int, 'grasas_g' => int]]
     */
    public function calculate(ClientProfile $profile): array
    {
        $weight = $profile->weightKg ?? self::DEFAULT_WEIGHT_KG;
        $height = $profile->heightCm ?? self::DEFAULT_HEIGHT_CM;
        $age = $profile->age ?? self::DEFAULT_AGE;
        $gender = $this->normalizeGender($profile->gender);
        $level = $profile->level ?? 'intermedio';
        $goal = $profile->goal ?? 'mantenimiento';

        $bmr = $this->mifflinStJeor($weight, $height, $age, $gender);
        $activityFactor = self::ACTIVITY_FACTOR[$level] ?? 1.55;
        $tdee = (int) round($bmr * $activityFactor);

        $kcalAdjustment = $this->kcalAdjustmentForGoal($goal);
        $objetivoCal = max(1200, $tdee + $kcalAdjustment); // piso seguridad

        $macros = $this->macrosForGoal($weight, $objetivoCal, $goal);

        return [
            'bmr' => (int) round($bmr),
            'tdee' => $tdee,
            'objetivo_cal' => $objetivoCal,
            'macros' => $macros,
            'meta' => [
                'weight_used_kg' => $weight,
                'height_used_cm' => $height,
                'age_used' => $age,
                'gender_used' => $gender,
                'level_used' => $level,
                'goal_used' => $goal,
                'activity_factor' => $activityFactor,
                'kcal_adjustment' => $kcalAdjustment,
            ],
        ];
    }

    /**
     * Mifflin-St Jeor:
     *   Hombre: 10·peso + 6.25·altura − 5·edad + 5
     *   Mujer:  10·peso + 6.25·altura − 5·edad − 161
     */
    private function mifflinStJeor(float $weight, float $height, int $age, string $gender): float
    {
        $base = (10.0 * $weight) + (6.25 * $height) - (5.0 * $age);
        return $gender === 'F' ? $base - 161.0 : $base + 5.0;
    }

    private function kcalAdjustmentForGoal(string $goal): int
    {
        return match ($goal) {
            'perdida_grasa' => -400,
            'recomposicion' => -150,
            'mantenimiento' => 0,
            'hipertrofia' => 250,
            'fuerza' => 200,
            default => 0,
        };
    }

    /**
     * @return array{proteina_g: int, carbohidratos_g: int, grasas_g: int}
     */
    private function macrosForGoal(float $weight, int $objetivoCal, string $goal): array
    {
        // Proteína g/kg según goal.
        $proteinPerKg = match ($goal) {
            'perdida_grasa' => 2.4, // alta para preservar masa en déficit
            'recomposicion' => 2.2,
            'hipertrofia' => 2.0,
            'fuerza' => 2.0,
            'mantenimiento' => 1.8,
            default => 2.0,
        };

        $proteinaG = (int) round($weight * $proteinPerKg);

        // Grasas: 0.9 g/kg base. Sale ~28-33% de kcal.
        $grasasG = (int) round($weight * 0.9);

        $kcalFromProtein = $proteinaG * 4;
        $kcalFromFat = $grasasG * 9;
        $kcalRemaining = max(0, $objetivoCal - $kcalFromProtein - $kcalFromFat);
        $carbohidratosG = (int) round($kcalRemaining / 4);

        return [
            'proteina_g' => $proteinaG,
            'carbohidratos_g' => $carbohidratosG,
            'grasas_g' => $grasasG,
        ];
    }

    private function normalizeGender(?string $g): string
    {
        if ($g === null) {
            return 'M';
        }
        $lower = strtolower($g);
        return in_array($lower, ['f', 'femenino', 'female', 'mujer'], true) ? 'F' : 'M';
    }
}
