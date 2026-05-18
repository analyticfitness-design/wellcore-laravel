<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Nutrition\Data;

/**
 * Una opción de comida: 2-4 FoodItem que suman ~el target del slot.
 *
 * El cliente verá 3 opciones por slot (A, B, C) para variedad.
 */
final readonly class MealOption
{
    /**
     * @param FoodItem[] $items
     */
    public function __construct(
        public array $items,
        public int $totalProteinaG,
        public int $totalCarbosG,
        public int $totalGrasasG,
        public int $totalKcal,
    ) {
    }

    /**
     * @return string[] Labels para opcion_a array de strings.
     */
    public function toLabels(): array
    {
        return array_map(fn (FoodItem $i) => $i->toLabel(), $this->items);
    }
}
