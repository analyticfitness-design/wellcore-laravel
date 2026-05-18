<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Nutrition\Data;

/**
 * Item de comida: un alimento con su porción en gramos calculada.
 *
 * El cliente lo verá como "Pechuga de pollo (150g)" en la opción de la comida.
 * El label limpia el sufijo "(crudo)" del name canónico porque el cliente come
 * la versión preparada — no le interesa pesar en crudo a menos que el coach lo pida.
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
        // Limpia "(crudo)", "(cocida)", "(cocido)" del nombre — el cliente no necesita ver el estado.
        // Los gramos siempre se calcularon en crudo (datos del catálogo) pero presentar al cliente
        // sin esta etiqueta hace que el plan se vea más profesional y menos técnico.
        $cleanName = (string) preg_replace('/\s*\((crudo|cocida|cocido|seco|seca)\)\s*$/iu', '', $this->foodName);
        return "{$cleanName} ({$this->portionG}g)";
    }
}
