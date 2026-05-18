<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Nutrition;

use App\Services\ComposeEngine\Data\ComposeContext;
use App\Services\ComposeEngine\Data\ComposeResult;
use App\Services\ComposeEngine\Nutrition\Data\MealOption;
use App\Services\ComposeEngine\Nutrition\Data\MealSlot;
use App\Services\ComposeEngine\Principles\PrincipleInjector;

/**
 * Compose stage para vertical=nutricion.
 *
 * Pipeline:
 *   1. MacroCalculator → BMR, TDEE, objetivo_cal, macros (P/C/F)
 *   2. MealsBuilder → 5 slots de comida con target macros por slot
 *   3. FoodSelector → 3 opciones (A/B/C) por slot
 *   4. Ensamblar JSON con shape compatible con LintEngine
 *
 * Shape canónico (matches sample expected por lint rules):
 *   {
 *     plan_type: "nutricion",
 *     objetivo_cal: 2400,
 *     macros: { proteina_g, carbohidratos_g, grasas_g },
 *     comidas: [
 *       {
 *         nombre: "Desayuno",
 *         hora: "07:00",
 *         macros: { proteina, carbohidratos, grasas },  ← SIN _g (lint rule)
 *         opcion_a: ["Huevos enteros (3 unidades, 150g)", ...],
 *         opcion_b: [...],
 *         opcion_c: [...]
 *       }, ...
 *     ]
 *   }
 */
final class NutritionPlanComposer
{
    public function __construct(
        private readonly MacroCalculator $macros,
        private readonly MealsBuilder $meals,
        private readonly FoodSelector $foods,
        private readonly PrincipleInjector $principleInjector,
    ) {
    }

    public function compose(ComposeContext $ctx): ComposeResult
    {
        $start = microtime(true);
        $warnings = [];

        // 1. Calcular macros diarios.
        $macroPlan = $this->macros->calculate($ctx->profile);

        // 2. Build meal slots. Consume preferences.num_meals y preferences.meal_times
        //    (provenientes del coach_brief.plan_nutricional o flags CLI explícitos).
        $prefs = $ctx->profile->preferences ?? [];
        $mealsCount = isset($prefs['num_meals']) ? (int) $prefs['num_meals'] : 5;
        if (! in_array($mealsCount, MealsBuilder::SUPPORTED_COUNTS, true)) {
            $warnings[] = "num_meals={$mealsCount} no soportado (válidos: 3,4,5,6). Caigo a 5.";
            $mealsCount = 5;
        }
        $customTimes = isset($prefs['meal_times']) && is_array($prefs['meal_times'])
            ? $prefs['meal_times']
            : null;
        if ($customTimes !== null && count($customTimes) !== $mealsCount) {
            $warnings[] = 'meal_times count (' . count($customTimes) . ") no matchea num_meals ({$mealsCount}). Ignoro custom_times y uso horarios canónicos.";
            $customTimes = null;
        }
        $slots = $this->meals->build($macroPlan, $mealsCount, $customTimes);

        // 3. Para cada slot, generar opciones A/B/C.
        $comidas = [];
        foreach ($slots as $slot) {
            $options = $this->foods->selectForSlot($slot, $ctx->profile);
            if (count($options) < 3) {
                $warnings[] = "Comida '{$slot->name}': solo {gencount} opciones disponibles (objetivo 3) — el catálogo es chico para las restricciones del cliente.";
            }
            $comidas[] = $this->buildMealJson($slot, $options);
        }

        // Sprint 34: inyectar principles relevantes
        $injectedPrinciples = $this->principleInjector->selectTop($ctx->profile, 'nutricion', limit: 3);
        $extraTips = $this->principleInjector->asTipsArray($injectedPrinciples);

        $planJson = [
            'plan_type' => 'nutricion',
            'titulo' => $this->buildTitle($ctx),
            'objetivo' => $this->buildObjetivo($ctx, $macroPlan),
            'metodologia' => (string) $ctx->methodology->name,
            'duracion_semanas' => 4, // mensual canónico
            'fecha_inicio' => $ctx->fechaInicio,
            'objetivo_cal' => $macroPlan['objetivo_cal'],
            'macros' => [
                'proteina_g' => $macroPlan['macros']['proteina_g'],
                'carbohidratos_g' => $macroPlan['macros']['carbohidratos_g'],
                'grasas_g' => $macroPlan['macros']['grasas_g'],
            ],
            'tdee_calculado' => $macroPlan['tdee'],
            'bmr_calculado' => $macroPlan['bmr'],
            'notas_coach' => $this->buildNotasCoach($ctx, $macroPlan),
            'tips' => array_merge($this->buildTips($ctx, $macroPlan), $extraTips),
            'principios_aplicados' => $injectedPrinciples->pluck('slug')->toArray(),
            'comidas' => $comidas,
        ];

        return new ComposeResult(
            planJson: $planJson,
            warnings: $warnings,
            durationMs: (microtime(true) - $start) * 1000,
        );
    }

    /**
     * @param MealOption[] $options
     */
    private function buildMealJson(MealSlot $slot, array $options): array
    {
        $meal = [
            'nombre' => $slot->name,
            'hora' => $slot->horaSugerida,
            'macros' => [
                'proteina' => $slot->targetProteinaG,        // sin _g (lint rule canonical)
                'carbohidratos' => $slot->targetCarbosG,
                'grasas' => $slot->targetGrasasG,
            ],
            'kcal_objetivo' => $slot->targetKcal,
        ];

        $keys = ['opcion_a', 'opcion_b', 'opcion_c'];
        foreach ($keys as $i => $key) {
            if (isset($options[$i])) {
                $meal[$key] = $options[$i]->toLabels();
            }
        }

        return $meal;
    }

    private function buildTitle(ComposeContext $ctx): string
    {
        $base = "Plan {$ctx->methodology->name}";
        return $ctx->clientName !== null ? "{$base} — {$ctx->clientName}" : $base;
    }

    private function buildObjetivo(ComposeContext $ctx, array $macroPlan): string
    {
        $goal = $ctx->profile->goal;
        $kcal = $macroPlan['objetivo_cal'];

        $goalText = match ($goal) {
            'perdida_grasa' => "Pérdida de grasa con preservación de masa muscular ($kcal kcal/día, déficit moderado)",
            'recomposicion' => "Recomposición corporal: bajar grasa preservando músculo ($kcal kcal/día)",
            'mantenimiento' => "Mantenimiento de masa magra y rendimiento ($kcal kcal/día)",
            'hipertrofia' => "Ganar masa muscular en superávit ligero ($kcal kcal/día)",
            default => "Mejorar composición corporal ($kcal kcal/día)",
        };

        return $goalText . '.';
    }

    private function buildNotasCoach(ComposeContext $ctx, array $macroPlan): string
    {
        $coach = $ctx->coachName ?? 'tu coach';
        $proteina = $macroPlan['macros']['proteina_g'];
        return "El plan está calculado con tu peso y objetivo. Las 3 opciones por comida son intercambiables — usá la que tengas más a mano ese día. La proteína es no-negociable: tu objetivo diario son {$proteina}g distribuidos en las 5 comidas. Si te quedás corto de carbos o grasas algún día, no es problema; si te quedás corto de proteína, sí. — $coach";
    }

    /**
     * @return string[]
     */
    private function buildTips(ComposeContext $ctx, array $macroPlan): array
    {
        $kcal = $macroPlan['objetivo_cal'];
        $proteina = $macroPlan['macros']['proteina_g'];

        return [
            "Tomá mínimo " . round(($ctx->profile->weightKg ?? 70) * 0.035, 1) . " L de agua al día (35 ml/kg)",
            "Si entrenás, tomá la comida pre-entreno 60-90 min antes",
            "Anotá el peso 1 vez por semana en ayunas, no diario (mucha varianza)",
            "Las gramaturas son crudo/seco — pesá antes de cocinar",
            "Si te saltás una comida, sumá su proteína (~" . round($proteina / 5) . "g) a la siguiente",
        ];
    }
}
