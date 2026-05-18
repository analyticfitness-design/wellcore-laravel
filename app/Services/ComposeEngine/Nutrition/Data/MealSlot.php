<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Nutrition\Data;

/**
 * Una comida del plan: nombre + % del total diario + macros target.
 *
 * `kcalShare` es el porcentaje del objetivo_cal diario que va a esta comida
 * (ej. 0.30 = 30%). Se usa para calcular el macro target específico de la
 * comida (sumando todos los slots debe dar ~100%).
 */
final readonly class MealSlot
{
    public function __construct(
        public string $name,
        public string $horaSugerida,
        public float $kcalShare,
        public int $targetKcal,
        public int $targetProteinaG,
        public int $targetCarbosG,
        public int $targetGrasasG,
    ) {
    }
}
