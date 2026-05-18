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
        private readonly ?\App\Services\ComposeEngine\Coach\CoachNotesBuilder $coachNotesBuilder = null,
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

        $coachNotes = $this->coachNotesBuilder
            ? $this->coachNotesBuilder->buildForNutricion(
                $ctx->profile,
                $ctx->clientName,
                $ctx->coachName,
                $macroPlan,
                $mealsCount,
            )
            : $this->buildNotasCoach($ctx, $macroPlan);

        $planJson = [
            'plan_type' => 'nutricion',
            'titulo' => $this->buildTitle($ctx),
            'objetivo' => $this->buildObjetivoEnriquecido($ctx, $macroPlan),
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
            'hidratacion' => $this->buildHidratacion($ctx->profile),
            'notas_coach' => $coachNotes,
            'consejos_coach' => $this->buildConsejosCoach($ctx->profile),
            'tips' => array_merge($this->buildTips($ctx, $macroPlan), $extraTips),
            'principios_aplicados' => $injectedPrinciples->pluck('slug')->toArray(),
            'comidas' => $comidas,
            'plan_dia_descanso' => $this->buildPlanDiaDescanso($macroPlan),
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
            'subtitulo' => $this->buildMealSubtitulo($slot->name),
            'macros' => [
                'proteina' => $slot->targetProteinaG,        // sin _g (lint rule canonical)
                'carbohidratos' => $slot->targetCarbosG,
                'grasas' => $slot->targetGrasasG,
            ],
            'kcal_objetivo' => $slot->targetKcal,
            'notas' => $this->buildMealNotas($slot->name),
        ];

        $keys = ['opcion_a', 'opcion_b', 'opcion_c'];
        foreach ($keys as $i => $key) {
            if (isset($options[$i])) {
                $meal[$key] = $options[$i]->toLabels();
            }
        }

        return $meal;
    }

    /**
     * Subtítulo descriptivo por slot ("Proteína completa + carbo medio + 1 fruta")
     */
    private function buildMealSubtitulo(string $slotName): string
    {
        $name = mb_strtolower($slotName, 'UTF-8');
        return match (true) {
            str_contains($name, 'desayuno') => 'Proteína + carbohidrato + fruta',
            str_contains($name, 'snack am'), str_contains($name, 'media manana') => 'Algo proteico ligero con grasa buena o fruta',
            str_contains($name, 'almuerzo') => 'Tu comida más fuerte del día: proteína, carbohidrato y verdura',
            str_contains($name, 'merienda') => 'Snack proteico de la tarde',
            str_contains($name, 'pre-entreno'), str_contains($name, 'pre entreno') => 'Energía rápida (fruta o pan) + proteína liviana, sin grasa',
            str_contains($name, 'cena') => 'Proteína con verdura, ligero, 2-3h antes de dormir',
            str_contains($name, 'post-entreno'), str_contains($name, 'post entreno') => 'Proteína + carbohidrato en la primera media hora post-entreno',
            default => 'Comida balanceada',
        };
    }

    /**
     * Notas "POR QUÉ" por slot — voz personal del coach.
     */
    private function buildMealNotas(string $slotName): string
    {
        $name = mb_strtolower($slotName, 'UTF-8');
        return match (true) {
            str_contains($name, 'desayuno') => 'La proteína la cocinás sin aceite — usá sartén antiadherente o spray en aerosol. El carbohidrato y la fruta te dan la energía para arrancar el día y entrenar 2-3 horas después.',
            str_contains($name, 'snack am'), str_contains($name, 'media manana') => 'Snack pequeño para mantener proteína repartida. Si no tenés hambre, sumá esta proteína al desayuno o almuerzo — no te la saltés del todo.',
            str_contains($name, 'almuerzo') => 'Es la comida más grande del día. El aceite de oliva en frío (una cucharada) va para la ensalada o el aguacate, no para freír. La proteína la hacés a la plancha, al horno o hervida — nada de frito.',
            str_contains($name, 'merienda') => 'Snack de mitad de tarde. Si entrenás de noche, movélo a pre-entreno (una hora antes).',
            str_contains($name, 'pre-entreno'), str_contains($name, 'pre entreno') => 'Algo con energía rápida (fruta o pan) + proteína liviana. Sin aceite ni grasas — para que digiera rápido y entrenes ligero. 30-45 min antes del gym.',
            str_contains($name, 'cena') => 'Si llegás tarde y con sueño, comé solo proteína + verdura — la cena es la comida más flexible. Evitá carbohidratos pesados si dormís dentro de 2 horas.',
            str_contains($name, 'post-entreno'), str_contains($name, 'post entreno') => 'Apenas terminás el entreno tenés 30 min. Si hoy no vas al gym, saltátela y sumale la proteína a la próxima comida.',
            default => 'Las 3 opciones tienen lo mismo nutricionalmente — elegí la que más se te antoje o la que tengas a mano.',
        };
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
            "Tomá mínimo " . round(($ctx->profile->weightKg ?? 70) * 0.035, 1) . " L de agua al día (tu peso en kilos × 35 ml = tu mínimo)",
            "Si entrenás, comé la comida pre-entreno entre 60 y 90 min antes",
            "Pesate 1 vez por semana en ayunas. Diario te vuelve loca con números que no significan nada — varía mucho por agua y comida",
            "Los gramos que te pongo son del alimento crudo o seco — pesalo antes de cocinarlo",
            "Si te saltás una comida, sumale la proteína (~" . round($proteina / 5) . "g) a la siguiente",
        ];
    }

    /**
     * Objetivo enriquecido — texto largo razonado, voz directa al cliente.
     */
    private function buildObjetivoEnriquecido(ComposeContext $ctx, array $macroPlan): string
    {
        $goal = $ctx->profile->goal;
        $kcal = (int) $macroPlan['objetivo_cal'];
        $tdee = (int) ($macroPlan['tdee'] ?? 0);
        $proteinaG = (int) ($macroPlan['macros']['proteina_g'] ?? 0);
        $weight = $ctx->profile->weightKg ?? 0;
        $proteinaPorKg = $weight > 0 ? round($proteinaG / $weight, 1) : 0;
        $delta = $tdee - $kcal;

        return match ($goal) {
            'perdida_grasa' => "Vas a bajar grasa sin perder músculo. Comés {$kcal} kcal por día con proteína alta ({$proteinaPorKg} g por cada kilo tuyo = {$proteinaG}g total) para que el cuerpo conserve el músculo. Meta: bajar entre medio y un kilo por semana, de la semana 2 en adelante.",
            'recomposicion' => "Vas a bajar grasa y mantener músculo al mismo tiempo. Comés {$kcal} kcal por día con proteína alta ({$proteinaPorKg} g por cada kilo tuyo = {$proteinaG}g total). La balanza casi no se mueve pero el espejo cambia.",
            'mantenimiento' => "Mantenés tu forma física actual. {$kcal} kcal por día con proteína {$proteinaPorKg} g por cada kilo tuyo = {$proteinaG}g total.",
            'hipertrofia' => "Vas a ganar masa muscular. Comés {$kcal} kcal por día con proteína {$proteinaPorKg} g por cada kilo tuyo = {$proteinaG}g total. Meta: subir entre 300 y 500 gramos por semana, de la semana 2 en adelante.",
            default => "{$kcal} kcal por día con {$proteinaG}g de proteína ({$proteinaPorKg} g por cada kilo tuyo).",
        };
    }

    /**
     * Hidratación recomendada por peso (35 ml/kg base + 500 ml por hora de entreno).
     */
    private function buildHidratacion(\App\Services\DecisionEngine\Data\ClientProfile $profile): array
    {
        $weight = $profile->weightKg ?? 70;
        $litrosBase = round($weight * 0.035, 1);
        return [
            'agua_minima_litros' => $litrosBase,
            'agua_total_dia_entreno_litros' => round($litrosBase + 0.5, 1),
            'electrolitos' => $profile->goal === 'perdida_grasa'
                ? 'Si estás comiendo menos calorías y entrenás más de 45 min o sumás cardio, agregale una pizca de sal al agua. Te evita el bajón.'
                : 'Una pizca de sal con limón en un vaso de agua al levantarte ayuda a la hidratación y al entreno.',
            'notas' => "Tu peso en kilos × 35 ml te da el mínimo diario: {$litrosBase} L. Sumá 500 ml extra los días que entrenás.",
        ];
    }

    /**
     * Plan ajustado para día de descanso (cliente NO entrena ese día).
     */
    private function buildPlanDiaDescanso(array $macroPlan): array
    {
        $kcalNormal = (int) $macroPlan['objetivo_cal'];
        $kcalDescanso = max($kcalNormal - 150, 1200); // 150 kcal menos en día sin entreno

        return [
            'descripcion' => 'Los días que no entrenás bajás un poco las calorías porque no quemás extra. La proteína se mantiene igual; bajás un poco los carbohidratos.',
            'calorias_objetivo' => $kcalDescanso,
            'ajustes' => [
                'Reducí ~30g de arroz o pasta en el almuerzo (te queda ~120 kcal menos)',
                'Mantené la proteína igual — el músculo también se construye en descanso',
                'Si tenés snack pre-entreno, saltátelo (esa comida es para tener energía en el gym)',
                'Hidratate igual: el descanso es cuando el cuerpo procesa todo lo trabajado',
            ],
        ];
    }

    /**
     * Bullets accionables (CONSEJOS DE TU COACH) — replica el estándar 8/10.
     *
     * @return string[]
     */
    private function buildConsejosCoach(\App\Services\DecisionEngine\Data\ClientProfile $profile): array
    {
        $weight = $profile->weightKg ?? 70;
        $litrosBase = round($weight * 0.035, 1);
        $base = [
            'La proteína no se negocia: si llegás al total del día, el día está hecho',
            'Las 3 opciones de cada comida valen lo mismo — cambialas como quieras',
            'Cociná sin aceite — plancha, horno o vapor. Si necesitás, spray en aerosol o sartén antiadherente',
            'Verduras de ensalada = libres. Llenate el plato sin contar gramos',
            'Cocinar para varios días el domingo te salva la semana',
            "{$litrosBase} L de agua mínimo al día. El hambre muchas veces es sed",
            'Café y té libres, sin azúcar',
            'Si te ataca el antojo de noche: té con canela o agua caliente con una cucharadita de miel',
        ];

        return match ($profile->goal) {
            'perdida_grasa' => array_merge($base, [
                'Días que entrenás, sumá 50 kcal de carbohidratos. Días sin entreno, restalas',
                'Una comida libre por semana — UNA comida, no todo el día',
                'Si el peso no baja 2 semanas seguidas, escribime y ajustamos',
            ]),
            'hipertrofia' => array_merge($base, [
                'Días que entrenás, sumá entre 100 y 150 kcal de carbohidratos (pre y post entreno)',
                'Apenas terminás el entreno, comé algo con proteína en la primera media hora',
            ]),
            default => $base,
        };
    }
}
