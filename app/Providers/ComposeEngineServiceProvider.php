<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\ComposeEngine\ComposeEngine;
use App\Services\ComposeEngine\Exercises\ExerciseSelector;
use App\Services\ComposeEngine\Nutrition\FoodSelector;
use App\Services\ComposeEngine\Nutrition\MacroCalculator;
use App\Services\ComposeEngine\Nutrition\MealsBuilder;
use App\Services\ComposeEngine\Nutrition\NutritionPlanComposer;
use App\Services\ComposeEngine\Periodization\PeriodizationApplier;
use App\Services\ComposeEngine\PlanComposer;
use App\Services\ComposeEngine\Principles\PrincipleInjector;
use App\Services\ComposeEngine\Splits\SplitBuilder;
use App\Services\ComposeEngine\Cycle\CycleModulationComposer;
use App\Services\ComposeEngine\Habits\HabitsPlanComposer;
use App\Services\ComposeEngine\Supplementation\StackSelector;
use App\Services\ComposeEngine\Supplementation\SupplementPlanComposer;
use Illuminate\Support\ServiceProvider;

/**
 * Registra el ComposeEngine (Stage 3 COMPOSE del motor v2) como singleton.
 *
 * Sprint 4: PlanComposer (vertical=entrenamiento)
 * Sprint 7: NutritionPlanComposer (vertical=nutricion)
 */
final class ComposeEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Entrenamiento
        $this->app->singleton(SplitBuilder::class);
        $this->app->singleton(ExerciseSelector::class);
        $this->app->singleton(PeriodizationApplier::class);

        // PrincipleInjector (Sprint 32+34) — usado por TODOS los composers
        $this->app->singleton(PrincipleInjector::class);

        $this->app->singleton(PlanComposer::class, function ($app) {
            return new PlanComposer(
                splitBuilder: $app->make(SplitBuilder::class),
                exerciseSelector: $app->make(ExerciseSelector::class),
                periodization: $app->make(PeriodizationApplier::class),
                principleInjector: $app->make(PrincipleInjector::class),
            );
        });

        // Nutrición (Sprint 7)
        $this->app->singleton(MacroCalculator::class);
        $this->app->singleton(MealsBuilder::class);
        $this->app->singleton(FoodSelector::class);

        $this->app->singleton(NutritionPlanComposer::class, function ($app) {
            return new NutritionPlanComposer(
                macros: $app->make(MacroCalculator::class),
                meals: $app->make(MealsBuilder::class),
                foods: $app->make(FoodSelector::class),
                principleInjector: $app->make(PrincipleInjector::class),
            );
        });

        // Suplementación (Sprint 13)
        $this->app->singleton(StackSelector::class);
        $this->app->singleton(SupplementPlanComposer::class, function ($app) {
            return new SupplementPlanComposer(
                selector: $app->make(StackSelector::class),
                principleInjector: $app->make(PrincipleInjector::class),
            );
        });

        // Hábitos (Sprint 16) — con PrincipleInjector (Sprint 34)
        $this->app->singleton(HabitsPlanComposer::class, function ($app) {
            return new HabitsPlanComposer(
                principleInjector: $app->make(PrincipleInjector::class),
            );
        });

        // Ciclo (Sprint 19) — con PrincipleInjector (Sprint 34)
        $this->app->singleton(CycleModulationComposer::class, function ($app) {
            return new CycleModulationComposer(
                principleInjector: $app->make(PrincipleInjector::class),
            );
        });

        $this->app->singleton(ComposeEngine::class, function ($app) {
            return new ComposeEngine(
                planComposer: $app->make(PlanComposer::class),
                nutritionComposer: $app->make(NutritionPlanComposer::class),
                supplementComposer: $app->make(SupplementPlanComposer::class),
                habitsComposer: $app->make(HabitsPlanComposer::class),
                cycleComposer: $app->make(CycleModulationComposer::class),
            );
        });
    }
}
