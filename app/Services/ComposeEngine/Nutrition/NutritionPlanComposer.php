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
            str_contains($name, 'desayuno') => 'Proteína completa + carbo medio + 1 fruta',
            str_contains($name, 'snack am'), str_contains($name, 'media manana') => 'Proteína magra + grasa saludable o fruta',
            str_contains($name, 'almuerzo') => 'Comida principal — proteína + carbo + verdura',
            str_contains($name, 'merienda') => 'Snack proteico + fruta',
            str_contains($name, 'pre-entreno'), str_contains($name, 'pre entreno') => 'Carbo rápido + proteína magra · sin grasa · 30-45 min antes',
            str_contains($name, 'cena') => 'Proteína + verdura · ligero · 2-3h antes de dormir',
            str_contains($name, 'post-entreno'), str_contains($name, 'post entreno') => 'Ventana anabólica · proteína + carbo · dentro de los 30 min',
            default => 'Comida balanceada',
        };
    }

    /**
     * Notas "POR QUÉ" por slot — explicación humana al cliente.
     */
    private function buildMealNotas(string $slotName): string
    {
        $name = mb_strtolower($slotName, 'UTF-8');
        return match (true) {
            str_contains($name, 'desayuno') => 'La proteína del desayuno la cocinás sin aceite — usá sartén antiadherente o spray. El carbo y la fruta te dan energía para arrancar el día y entrenar 2-3 horas después.',
            str_contains($name, 'snack am'), str_contains($name, 'media manana') => 'Snack pequeño para mantener proteína constante. Si no tenés hambre, sumá esta proteína al desayuno o almuerzo — no te la saltés del todo.',
            str_contains($name, 'almuerzo') => 'Comida más grande del día. Usá aceite de oliva en frío (1 cda) para la ensalada o aguacate, no para freír. La proteína a la plancha, horno o hervida — sin frituras.',
            str_contains($name, 'merienda') => 'Snack de mitad de tarde. Si entrenás de noche, podés moverlo a pre-entreno (1 hora antes).',
            str_contains($name, 'pre-entreno'), str_contains($name, 'pre entreno') => 'Tomá esta comida 30-45 min antes de entrenar. Sin grasas (retrasan digestión). Carbo rápido (banano, arepa, arroz) para tener energía y proteína magra (claras, pechuga).',
            str_contains($name, 'cena') => 'Si llegás tarde y con sueño, come solo proteína + verdura — la cena es la comida más flexible. Evitá carbos pesados si dormís dentro de 2h.',
            str_contains($name, 'post-entreno'), str_contains($name, 'post entreno') => 'Dentro de los 30 min después del entreno. Si no entrenás hoy, sáltate esta comida (sumá la proteína a la próxima).',
            default => 'Las 3 opciones son equivalentes en macros (±5%) — elegí la que más te guste o tengas en casa.',
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
            "Tomá mínimo " . round(($ctx->profile->weightKg ?? 70) * 0.035, 1) . " L de agua al día (35 ml/kg)",
            "Si entrenás, tomá la comida pre-entreno 60-90 min antes",
            "Anotá el peso 1 vez por semana en ayunas, no diario (mucha varianza)",
            "Las gramaturas son crudo/seco — pesá antes de cocinar",
            "Si te saltás una comida, sumá su proteína (~" . round($proteina / 5) . "g) a la siguiente",
        ];
    }

    /**
     * Objetivo enriquecido — texto largo razonado con TDEE/déficit/proteína g/kg.
     */
    private function buildObjetivoEnriquecido(ComposeContext $ctx, array $macroPlan): string
    {
        $goal = $ctx->profile->goal;
        $kcal = (int) $macroPlan['objetivo_cal'];
        $tdee = (int) ($macroPlan['tdee'] ?? 0);
        $bmr = (int) ($macroPlan['bmr'] ?? 0);
        $proteinaG = (int) ($macroPlan['macros']['proteina_g'] ?? 0);
        $weight = $ctx->profile->weightKg ?? 0;
        $proteinaPorKg = $weight > 0 ? round($proteinaG / $weight, 1) : 0;
        $delta = $tdee - $kcal;
        $deltaText = match (true) {
            $delta > 0 => "déficit moderado de {$delta} kcal sobre tu TDEE ({$tdee} kcal/día)",
            $delta < 0 => "superávit ligero de " . abs($delta) . " kcal sobre tu TDEE ({$tdee} kcal/día)",
            default => "mantenimiento sobre tu TDEE ({$tdee} kcal/día)",
        };

        return match ($goal) {
            'perdida_grasa' => "Pérdida de grasa con preservación muscular — {$deltaText}. Vas a comer {$kcal} kcal/día con proteína alta ({$proteinaPorKg} g/kg = {$proteinaG}g) para mantener masa magra. Meta: bajar 0.5-1 kg/semana después de la semana 2.",
            'recomposicion' => "Recomposición corporal — {$deltaText}. Comerás {$kcal} kcal/día con proteína alta ({$proteinaPorKg} g/kg = {$proteinaG}g). La balanza puede no cambiar mucho pero el espejo sí.",
            'mantenimiento' => "Mantenimiento de masa magra y rendimiento — {$deltaText}. {$kcal} kcal/día con proteína {$proteinaPorKg} g/kg = {$proteinaG}g.",
            'hipertrofia' => "Ganancia de masa muscular — {$deltaText}. {$kcal} kcal/día con proteína {$proteinaPorKg} g/kg = {$proteinaG}g. Meta: subir ~0.3-0.5 kg/semana después de la semana 2.",
            default => "{$kcal} kcal/día con proteína {$proteinaG}g ({$proteinaPorKg} g/kg). {$deltaText}.",
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
                ? 'En déficit, sumá una pizca de sal en agua si entrenás >45 min o tomás cardio extra. Ayuda a evitar bajón energético.'
                : 'Pizca de sal y limón en 1 vaso al levantarte ayuda a la hidratación + sodio para entreno.',
            'notas' => "Tu peso × 0.035 = {$litrosBase} L mínimo diario. Sumá 500 ml extra los días de entreno.",
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
            'descripcion' => 'En días de descanso (sin entreno) ajustás levemente las calorías porque no quemás extra. Mantenés proteína y bajás un poco los carbos.',
            'calorias_objetivo' => $kcalDescanso,
            'ajustes' => [
                "Reducí ~30g de arroz o pasta en el almuerzo (cambia ~120 kcal)",
                "Mantené la proteína igual — el músculo se construye también en descanso",
                "Si tenés snack pre-entreno, sáltalo (esa comida es para tener energía de gym)",
                "Hidratate igual: el descanso es cuando el cuerpo procesa todo lo trabajado",
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
            'Proteína primero: si cumplís el target diario, el día sigue siendo productivo',
            'Las 3 opciones por comida son intercambiables — macros equivalentes',
            'Cocina sin aceite (plancha, horno, vapor). Spray o sartén antiadherente',
            'Verduras de ensalada = libres. Llenate el plato',
            'Batch cooking dominical te salva la semana',
            "{$litrosBase} L de agua mínimo. El hambre muchas veces es sed",
            'Café y té libres, sin azúcar',
            'Antojo nocturno: té con canela o agua caliente con miel (1 cdita)',
        ];

        return match ($profile->goal) {
            'perdida_grasa' => array_merge($base, [
                'En días de entreno: 50 kcal extra carbos. Descanso: -50 kcal',
                'Cheat meal: 1 comida/semana (no día entero)',
                'Si el peso no baja 2 semanas seguidas, avisame — ajustamos',
            ]),
            'hipertrofia' => array_merge($base, [
                'En días de entreno: +100-150 kcal extra carbos pre y post',
                'Snack post-entreno con proteína dentro de los 30-45 min',
            ]),
            default => $base,
        };
    }
}
