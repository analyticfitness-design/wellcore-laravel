<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine;

use App\Models\Kb\ExerciseMetadata;
use App\Services\ComposeEngine\Data\ComposeContext;
use App\Services\ComposeEngine\Data\ComposeResult;
use App\Services\ComposeEngine\Data\SplitDay;
use App\Services\ComposeEngine\Exercises\ExerciseSelector;
use App\Services\ComposeEngine\Periodization\PeriodizationApplier;
use App\Services\ComposeEngine\Principles\PrincipleInjector;
use App\Services\ComposeEngine\Splits\SplitBuilder;

/**
 * Orquesta la composición de un plan de entrenamiento mensual (4 semanas).
 *
 * Pipeline:
 *   1. SplitBuilder → días + grupos musculares
 *   2. ExerciseSelector → 3-5 ejercicios por día (1-2 compounds + 2-3 isolations)
 *   3. PeriodizationApplier → fase + RIR + series/reps por semana
 *   4. Ensamblar JSON shape compatible con LintEngine (sample-good-plan.json)
 *
 * Output: array que respeta el contrato del JSON canónico (plan_type, semanas[], dias[], ejercicios[]).
 */
final class PlanComposer
{
    /** Default mensual = 4 semanas (memoria autoritativa feedback_planes_mensuales_solamente). */
    public const DURACION_MENSUAL = 4;

    public function __construct(
        private readonly SplitBuilder $splitBuilder,
        private readonly ExerciseSelector $exerciseSelector,
        private readonly PeriodizationApplier $periodization,
        private readonly PrincipleInjector $principleInjector,
    ) {
    }

    public function compose(ComposeContext $ctx): ComposeResult
    {
        $start = microtime(true);
        $warnings = [];

        $methodology = $ctx->methodology;
        $profile = $ctx->profile;

        // 1. Split del día. Si el profile trae preferences.split_override, lo respeta.
        $splitOverride = is_array($profile->preferences['split_override'] ?? null)
            ? $profile->preferences['split_override']
            : [];
        $splitDays = $this->splitBuilder->build(
            (string) $methodology->slug,
            $profile->gender,
            $profile->goal,
            $splitOverride,
        );

        // 2. Selección de ejercicios por día (una sola vez — se reusa en cada semana).
        /** @var array<string, ExerciseMetadata[]> $exercisesByDay */
        $exercisesByDay = [];
        foreach ($splitDays as $day) {
            $exercises = $this->exerciseSelector->selectForDay($day, $profile, $ctx->equipmentAvailable);
            if (count($exercises) < 3) {
                $warnings[] = "Día '{$day->dayName}' solo tiene " . count($exercises) . " ejercicios disponibles — el catálogo está incompleto para el muscle_target.";
            }
            $exercisesByDay[$day->dayName] = $exercises;
        }

        // 3. Periodización por semana.
        $weeks = $this->periodization->expand(
            $methodology->periodization_pattern ?? [],
            self::DURACION_MENSUAL,
        );

        // 4. Ensamblar JSON.
        $level = $profile->level ?? 'intermedio';
        $semanas = [];
        foreach ($weeks as $idx => $weekMeta) {
            $diasJson = [];
            $faseLabel = "{$weekMeta['fase']} · RIR {$weekMeta['rir']}";
            $setRepsCompound = $this->periodization->setRepsForPhase($weekMeta['fase'], $level);
            $setRepsIsolation = $this->periodization->setRepsForPhaseIsolation($weekMeta['fase'], $level);

            foreach ($splitDays as $day) {
                $diaJson = [
                    'dia_semana' => $day->dayName,
                    'grupo_muscular' => $day->groupLabel,
                    'ejercicios' => [],
                ];
                foreach ($exercisesByDay[$day->dayName] as $exercise) {
                    // Compounds llevan más series + menos reps que isolations.
                    // Esto diferencia el estímulo (fuerza vs hipertrofia local) y
                    // resuelve el warning heur_monotonia_3x12.
                    $setReps = $exercise->compound_isolation === 'compound'
                        ? $setRepsCompound
                        : $setRepsIsolation;

                    $diaJson['ejercicios'][] = [
                        'nombre' => $exercise->name_canonical,
                        'series' => $setReps['series'],
                        'repeticiones' => $setReps['reps'],
                        'descanso' => $setReps['descanso'],
                        'rir' => $weekMeta['rir'],
                        'gif_url' => $exercise->gifUrl(),
                    ];
                }
                $diasJson[] = $diaJson;
            }

            $semanas[] = [
                'numero' => $idx + 1,
                'fase' => $faseLabel,
                'dias' => $diasJson,
            ];
        }

        // Sprint 32: inyectar principles relevantes
        $injectedPrinciples = $this->principleInjector->selectTop($profile, 'entrenamiento', limit: 3);
        $extraTips = $this->principleInjector->asTipsArray($injectedPrinciples);

        $planJson = [
            'plan_type' => 'entrenamiento',
            'titulo' => $this->buildTitle($ctx),
            'objetivo' => $this->buildObjetivo($profile),
            'metodologia' => (string) $methodology->name,
            'frecuencia' => count($splitDays) . ' dias/semana',
            'frecuencia_dias' => count($splitDays),
            'duracion_semanas' => self::DURACION_MENSUAL,
            'fecha_inicio' => $ctx->fechaInicio,
            'split' => $this->buildSplitMap($splitDays),
            'notas_coach' => $this->buildNotasCoach($ctx),
            'tips' => array_merge($this->buildTips(), $extraTips),
            'principios_aplicados' => $injectedPrinciples->pluck('slug')->toArray(),
            'semanas' => $semanas,
        ];

        return new ComposeResult(
            planJson: $planJson,
            warnings: $warnings,
            durationMs: (microtime(true) - $start) * 1000,
        );
    }

    private function buildTitle(ComposeContext $ctx): string
    {
        $base = "Plan {$ctx->methodology->name}";
        return $ctx->clientName !== null ? "{$base} — {$ctx->clientName}" : $base;
    }

    private function buildObjetivo(\App\Services\DecisionEngine\Data\ClientProfile $profile): string
    {
        $goalText = match ($profile->goal) {
            'hipertrofia' => 'Ganar masa muscular con foco en hipertrofia',
            'fuerza' => 'Aumentar fuerza máxima en compuestos principales',
            'perdida_grasa' => 'Pérdida de grasa con preservación de masa muscular',
            'recomposicion' => 'Recomposición corporal: bajar grasa preservando músculo',
            'mantenimiento' => 'Mantener masa muscular y rendimiento',
            default => 'Mejorar composición corporal y rendimiento',
        };

        return $goalText . '.';
    }

    /**
     * @param SplitDay[] $days
     * @return array<string, string>
     */
    private function buildSplitMap(array $days): array
    {
        $map = [];
        foreach ($days as $day) {
            $map[$day->dayName] = $day->groupLabel;
        }
        return $map;
    }

    private function buildNotasCoach(ComposeContext $ctx): string
    {
        $coach = $ctx->coachName ?? 'tu coach';
        return "Este plan está armado para tu nivel y tus días disponibles. Anotá pesos y RIR cada sesión para medir progreso. Si una semana no llegás al RIR objetivo, te quedás en el peso y ajustás técnica primero. — $coach";
    }

    /**
     * @return string[]
     */
    private function buildTips(): array
    {
        return [
            'Anotá peso, reps y RIR de cada serie apenas terminás el ejercicio',
            'Hidratate durante el entreno (mínimo 500 ml por hora de gym)',
            'Dormí al menos 7 horas — la recuperación es parte del plan',
            'Si una articulación duele (no fatiga muscular), parás el ejercicio y avisás al coach',
        ];
    }
}
