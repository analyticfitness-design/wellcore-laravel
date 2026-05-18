<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine;

use App\Models\Kb\Methodology;
use App\Services\ComposeEngine\Data\ComposeContext;
use App\Services\ComposeEngine\Data\ComposeResult;
use App\Services\ComposeEngine\Cycle\CycleModulationComposer;
use App\Services\ComposeEngine\Habits\HabitsPlanComposer;
use App\Services\ComposeEngine\Nutrition\NutritionPlanComposer;
use App\Services\ComposeEngine\Supplementation\SupplementPlanComposer;
use App\Services\DecisionEngine\Data\ClientProfile;
use RuntimeException;

/**
 * Stage 3 COMPOSE del motor v2 — entry point.
 *
 * Despacha al composer apropiado según methodology.vertical:
 *   - entrenamiento → PlanComposer (Sprint 4)
 *   - nutricion     → NutritionPlanComposer (Sprint 7)
 *   - suplementacion → SupplementPlanComposer (Sprint 13)
 *
 * Otras verticales (habitos, ciclo) lanzan RuntimeException.
 */
final class ComposeEngine
{
    public function __construct(
        private readonly PlanComposer $planComposer,
        private readonly NutritionPlanComposer $nutritionComposer,
        private readonly SupplementPlanComposer $supplementComposer,
        private readonly HabitsPlanComposer $habitsComposer,
        private readonly CycleModulationComposer $cycleComposer,
    ) {
    }

    /**
     * @param string[] $equipmentAvailable
     */
    public function composeForMethodology(
        ClientProfile $profile,
        string $methodologySlug,
        string $fechaInicio,
        ?string $clientName = null,
        ?string $coachName = null,
        array $equipmentAvailable = ['gym_completo'],
    ): ComposeResult {
        $methodology = Methodology::query()
            ->where('slug', $methodologySlug)
            ->first();

        if ($methodology === null) {
            throw new RuntimeException(
                "ComposeEngine: methodology '$methodologySlug' no encontrada en wellcore_kb.methodologies."
            );
        }

        $context = new ComposeContext(
            profile: $profile,
            methodology: $methodology,
            fechaInicio: $fechaInicio,
            clientName: $clientName,
            coachName: $coachName,
            equipmentAvailable: $equipmentAvailable,
        );

        return match ($methodology->vertical) {
            'entrenamiento' => $this->planComposer->compose($context),
            'nutricion' => $this->nutritionComposer->compose($context),
            'suplementacion' => $this->supplementComposer->compose($context),
            'habitos' => $this->habitsComposer->compose($context),
            'ciclo' => $this->cycleComposer->compose($context),
            default => throw new RuntimeException(
                "ComposeEngine: vertical '{$methodology->vertical}' no soportada. " .
                "Disponibles en Sprint 19: entrenamiento, nutricion, suplementacion, habitos, ciclo."
            ),
        };
    }
}
