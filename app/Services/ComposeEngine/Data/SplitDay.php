<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Data;

/**
 * Un día del split: nombre del día + grupos musculares + qué músculos primarios buscar.
 *
 * `groupLabel` es lo que se muestra al cliente (ej. "Pecho + Tríceps + Core").
 * `muscleTargets` son las claves DB que usa el ExerciseSelector para query
 * (ej. ['Pecho', 'Tríceps', 'Core']).
 */
final readonly class SplitDay
{
    public function __construct(
        public string $dayName,
        public string $groupLabel,
        /** @var string[] muscle_primary keys del exercise_metadata */
        public array $muscleTargets,
    ) {
    }
}
