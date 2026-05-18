<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Nutrition\Data;

/**
 * Item de comida: un alimento con su porción en gramos calculada.
 *
 * El cliente lo verá como "Pechuga de pollo (150g)" en la opción de la comida.
 */
final readonly class FoodItem
{
    public function __construct(
        public string $foodSlug,
        public string $foodName,
        public int $portionG,
        public int $proteinaG,
        public int $carbosG,
        public int $grasasG,
        public int $kcal,
    ) {
    }

    public function toLabel(): string
    {
        return "{$this->foodName} ({$this->portionG}g)";
    }
}
