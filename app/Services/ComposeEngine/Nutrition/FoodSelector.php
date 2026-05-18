<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Nutrition;

use App\Models\Kb\NutritionFood;
use App\Services\ComposeEngine\Nutrition\Data\FoodItem;
use App\Services\ComposeEngine\Nutrition\Data\MealOption;
use App\Services\ComposeEngine\Nutrition\Data\MealSlot;
use App\Services\DecisionEngine\Data\ClientProfile;
use Illuminate\Support\Collection;

/**
 * Compone 3 opciones (A/B/C) por meal slot, usando patrones nutricionales
 * por tipo de comida (ver MealPatterns).
 *
 * Por opción:
 *   1. Pick proteína (categoría depende del slot — magra para pre-entreno, etc.)
 *   2. Pick carbo (grano integral en almuerzo, fruta en desayuno, etc.)
 *   3. (Opcional) Pick grasa (SI el pattern lo permite — pre-entreno NO)
 *   4. (Opcional) Pick fruta (desayuno)
 *   5. (Opcional) Pick vegetal (almuerzo/cena)
 *
 * Diversidad A/B/C:
 *   - Cada categoría se shuffle determinísticamente con seed
 *     (md5 de profile + slot.name → 3 índices distintos por categoría)
 *   - A, B, C usan índices distintos → alimentos diferentes
 *
 * Determinismo garantizado: mismo profile + slot → mismo output.
 */
final class FoodSelector
{
    /**
     * Construye 3 opciones (A, B, C) para un meal slot.
     *
     * @return MealOption[]
     */
    public function selectForSlot(MealSlot $slot, ClientProfile $profile): array
    {
        $pattern = MealPatterns::forSlot($slot->name);
        $restrictions = $profile->preferences;
        $excludedFoods = (array) ($restrictions['excluded_foods'] ?? []);
        $proteinKeyword = $this->resolveProteinKeyword($slot->name, $restrictions);

        // Carga catálogos solo de las categorías que el pattern indica.
        $proteins = $this->fetchByCategories($pattern['protein_categories'], $restrictions, $excludedFoods, $proteinKeyword);
        $carbs = $this->fetchByCategories($pattern['carb_categories'], $restrictions, $excludedFoods);
        $fats = $pattern['include_fat']
            ? $this->fetchByCategories(['grasa_oleaginosa', 'grasa_aceite', 'grasa_saludable'], $restrictions, $excludedFoods)
            : collect();
        $fruits = $pattern['include_fruit']
            ? $this->fetchByCategories(['fruta'], $restrictions, $excludedFoods)
            : collect();
        $vegetables = $pattern['include_vegetable']
            ? $this->fetchByCategories(['vegetal_hoja_verde', 'vegetal_crucifero', 'vegetal_otro'], $restrictions, $excludedFoods)
            : collect();

        // Seed determinístico para reproducibilidad (mismo profile+slot → mismo subset)
        $seed = $this->seedFromContext($profile, $slot->name);
        $proteinOrder = $this->deterministicOrder($proteins->count(), $seed);
        $carbOrder = $this->deterministicOrder($carbs->count(), $seed + 1);
        $fatOrder = $this->deterministicOrder($fats->count(), $seed + 2);
        $fruitOrder = $this->deterministicOrder($fruits->count(), $seed + 3);
        $vegOrder = $this->deterministicOrder($vegetables->count(), $seed + 4);

        $options = [];
        for ($variant = 0; $variant < 3; $variant++) {
            $option = $this->buildOption(
                $slot,
                $pattern,
                $proteins, $carbs, $fats, $fruits, $vegetables,
                $proteinOrder, $carbOrder, $fatOrder, $fruitOrder, $vegOrder,
                $variant,
            );
            if ($option !== null) {
                $options[] = $option;
            }
        }

        return $options;
    }

    /**
     * @param array<string,mixed> $pattern
     * @param int[] $proteinOrder
     * @param int[] $carbOrder
     * @param int[] $fatOrder
     * @param int[] $fruitOrder
     * @param int[] $vegOrder
     */
    private function buildOption(
        MealSlot $slot,
        array $pattern,
        Collection $proteins,
        Collection $carbs,
        Collection $fats,
        Collection $fruits,
        Collection $vegetables,
        array $proteinOrder,
        array $carbOrder,
        array $fatOrder,
        array $fruitOrder,
        array $vegOrder,
        int $variant,
    ): ?MealOption {
        $items = [];

        // 1. Proteína (rotamos por variant).
        $protein = $this->pick($proteins, $proteinOrder, $variant);
        if ($protein !== null && $slot->targetProteinaG > 0) {
            $items[] = $this->buildItemForMacro($protein, 'protein_g', $slot->targetProteinaG);
        }

        // 2. Carbohidrato (puede ser carb_grano o fruta según pattern).
        if ($slot->targetCarbosG > 5 && $carbs->isNotEmpty()) {
            $carbo = $this->pick($carbs, $carbOrder, $variant);
            if ($carbo !== null) {
                $carbosRemaining = max(0, $slot->targetCarbosG - $this->sumMacro($items, 'carbosG'));
                if ($carbosRemaining > 5) {
                    $items[] = $this->buildItemForMacro($carbo, 'carbs_g', $carbosRemaining);
                }
            }
        }

        // 3. Grasa (solo si pattern lo permite y target lo justifica).
        if ($pattern['include_fat'] && $slot->targetGrasasG >= 5 && $fats->isNotEmpty()) {
            $fat = $this->pick($fats, $fatOrder, $variant);
            if ($fat !== null) {
                $grasasRemaining = max(0, $slot->targetGrasasG - $this->sumMacro($items, 'grasasG'));
                if ($grasasRemaining > 2) {
                    $items[] = $this->buildItemForMacro($fat, 'fat_g', $grasasRemaining);
                }
            }
        }

        // 4. Fruta (solo desayuno generalmente).
        if ($pattern['include_fruit'] && $fruits->isNotEmpty()) {
            $fruit = $this->pick($fruits, $fruitOrder, $variant);
            if ($fruit !== null) {
                $items[] = $this->buildItemForPortion($fruit, 120); // 1 fruta mediana ~120g
            }
        }

        // 5. Vegetal (almuerzo/cena).
        if ($pattern['include_vegetable'] && $vegetables->isNotEmpty()) {
            $veg = $this->pick($vegetables, $vegOrder, $variant);
            if ($veg !== null) {
                $items[] = $this->buildItemForPortion($veg, 100); // 100g vegetal
            }
        }

        if ($items === []) {
            return null;
        }

        $totals = $this->sumItems($items);
        return new MealOption(
            items: $items,
            totalProteinaG: $totals['proteinaG'],
            totalCarbosG: $totals['carbosG'],
            totalGrasasG: $totals['grasasG'],
            totalKcal: $totals['kcal'],
        );
    }

    private function pick(Collection $items, array $order, int $variant): ?NutritionFood
    {
        if ($items->isEmpty() || $order === []) {
            return null;
        }
        $idx = $order[$variant % count($order)];
        return $items->get($idx);
    }

    /**
     * Construye un FoodItem ajustando portionG para alcanzar el target del macro indicado.
     */
    private function buildItemForMacro(NutritionFood $food, string $macroColumn, int $targetGrams): FoodItem
    {
        $macroPer100 = (float) $food->{$macroColumn};
        if ($macroPer100 <= 0) {
            return $this->buildItemForPortion($food, 100);
        }
        $portion = (int) round(($targetGrams / $macroPer100) * 100);

        // Cap inteligente por portion_typical: si el alimento define una porción típica máxima realista
        // (ej. claras=150g, tofu=200g, almendras=30g), respetar ese tope para evitar gramajes absurdos.
        $maxRealistic = $this->resolveMaxRealisticPortion($food);
        $portion = max(20, min($maxRealistic, $portion));

        return $this->buildItemForPortion($food, $portion);
    }

    /**
     * Resuelve el peso máximo realista para una porción según portion_typical del catálogo.
     * Si el food no define portion_typical o no se puede parsear, cae a 400g (cap legacy).
     */
    private function resolveMaxRealisticPortion(NutritionFood $food): int
    {
        $pt = $food->portion_typical;
        $portionTypical = is_array($pt) ? $pt : null;
        if (! $portionTypical) {
            return 400;
        }

        // Buscar claves canónicas: porcion_grande, max, comun (en orden de preferencia para hard cap).
        // porcion_grande es el techo real; comun es el promedio (no usamos como techo).
        $candidates = [
            $portionTypical['porcion_grande'] ?? null,
            $portionTypical['max'] ?? null,
            $portionTypical['grande'] ?? null,
        ];

        foreach ($candidates as $raw) {
            if (! is_string($raw) || $raw === '') {
                continue;
            }
            // Parsear gramos del string. Acepta "150g", "~150g", "120-200g", "200g (~150g cocido)"
            if (preg_match('/(\d{2,4})\s*g/u', $raw, $m)) {
                $grams = (int) $m[1];
                // Si hay rango "120-200g" tomar el max
                if (preg_match('/(\d{2,4})\s*-\s*(\d{2,4})\s*g/u', $raw, $range)) {
                    $grams = max((int) $range[1], (int) $range[2]);
                }
                if ($grams >= 30 && $grams <= 500) {
                    return $grams;
                }
            }
        }

        return 400;
    }

    private function buildItemForPortion(NutritionFood $food, int $portionG): FoodItem
    {
        $factor = $portionG / 100.0;
        return new FoodItem(
            foodSlug: (string) $food->slug,
            foodName: (string) $food->name,
            portionG: $portionG,
            proteinaG: (int) round((float) $food->protein_g * $factor),
            carbosG: (int) round((float) $food->carbs_g * $factor),
            grasasG: (int) round((float) $food->fat_g * $factor),
            kcal: (int) round((float) $food->kcal * $factor),
        );
    }

    /**
     * @param FoodItem[] $items
     */
    private function sumMacro(array $items, string $key): int
    {
        return array_sum(array_map(fn (FoodItem $i) => $i->{$key}, $items));
    }

    /**
     * @param FoodItem[] $items
     * @return array{proteinaG: int, carbosG: int, grasasG: int, kcal: int}
     */
    private function sumItems(array $items): array
    {
        return [
            'proteinaG' => $this->sumMacro($items, 'proteinaG'),
            'carbosG' => $this->sumMacro($items, 'carbosG'),
            'grasasG' => $this->sumMacro($items, 'grasasG'),
            'kcal' => $this->sumMacro($items, 'kcal'),
        ];
    }

    /**
     * @param string[] $categories
     * @param array<int|string, mixed> $restrictions
     * @param string[] $excludedFoods
     */
    private function fetchByCategories(
        array $categories,
        array $restrictions,
        array $excludedFoods = [],
        ?string $keyword = null,
    ): Collection {
        if ($categories === []) {
            return collect();
        }
        $q = NutritionFood::query()
            ->active()
            ->whereIn('category', $categories)
            ->respectingRestrictions($restrictions)
            ->excludingFoods($excludedFoods);

        if ($keyword !== null) {
            $filtered = (clone $q)->matchingKeyword($keyword)->orderBy('id')->get();
            // Si el keyword no devuelve nada utilizable, fallback al catálogo completo
            if ($filtered->isNotEmpty()) {
                return $filtered;
            }
        }

        return $q->orderBy('id')->get();
    }

    /**
     * Resuelve la keyword de proteína preferida para un slot dado.
     * preferences.meal_protein = ['desayuno' => 'huevos', 'almuerzo' => 'pollo', ...]
     */
    private function resolveProteinKeyword(string $slotName, array $preferences): ?string
    {
        $map = $preferences['meal_protein'] ?? null;
        if (! is_array($map) || $map === []) {
            return null;
        }
        $key = mb_strtolower($slotName);
        return $map[$key] ?? null;
    }

    /**
     * Seed determinístico desde el profile + slot. Mismo profile+slot → mismo orden.
     */
    private function seedFromContext(ClientProfile $profile, string $slotName): int
    {
        $key = ($profile->goal ?? '') . '|' . ($profile->level ?? '') . '|' . ($profile->gender ?? '') . '|' . $slotName;
        return (int) hexdec(substr(md5($key), 0, 8));
    }

    /**
     * Genera un orden determinístico de índices [0..n-1] shuffled con el seed.
     *
     * @return int[]
     */
    private function deterministicOrder(int $count, int $seed): array
    {
        if ($count === 0) {
            return [];
        }
        $indices = range(0, $count - 1);
        // Fisher-Yates determinístico con LCG simple
        $state = $seed;
        for ($i = $count - 1; $i > 0; $i--) {
            $state = ($state * 1103515245 + 12345) & 0x7FFFFFFF;
            $j = $state % ($i + 1);
            [$indices[$i], $indices[$j]] = [$indices[$j], $indices[$i]];
        }
        return $indices;
    }
}
