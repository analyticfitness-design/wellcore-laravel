<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine;

use App\Models\Kb\ExerciseMetadata;
use App\Services\ComposeEngine\Coach\CoachNotesBuilder;
use App\Services\ComposeEngine\Data\ComposeContext;
use App\Services\ComposeEngine\Data\ComposeResult;
use App\Services\ComposeEngine\Data\SplitDay;
use App\Services\ComposeEngine\Exercises\ExerciseNotesBuilder;
use App\Services\ComposeEngine\Exercises\ExerciseSelector;
use App\Services\ComposeEngine\Periodization\PeriodizationApplier;
use App\Services\ComposeEngine\Principles\PrincipleInjector;
use App\Services\ComposeEngine\Splits\SplitBuilder;

/**
 * Orquesta la composición de un plan de entrenamiento mensual (4 semanas).
 *
 * Pipeline:
 *   1. SplitBuilder → días + grupos musculares
 *   2. ExerciseSelector → ejercicios por día (volumen target por nivel)
 *   3. PeriodizationApplier → fase + RIR + series/reps por semana
 *   4. ExerciseNotesBuilder → enriquece cada ejercicio con notas técnicas (cues + mistakes + variación)
 *   5. CoachNotesBuilder → notas_coach personalizadas (4 párrafos: conexión, estrategia, qué esperar, acción)
 *   6. Aplica técnica de intensificación al último ejercicio del grupo según fase
 *   7. Ensambla JSON shape compatible con LintEngine + frontend
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
        private readonly ExerciseNotesBuilder $notesBuilder,
        private readonly CoachNotesBuilder $coachNotesBuilder,
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

        // 2. Selección de ejercicios por día.
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
            $weekNumber = $idx + 1;

            foreach ($splitDays as $day) {
                $ejercicios = $exercisesByDay[$day->dayName];
                $totalEj = count($ejercicios);
                $ejerciciosJson = [];

                foreach ($ejercicios as $position => $exercise) {
                    $isCompound = $exercise->compound_isolation === 'compound';
                    $setReps = $isCompound ? $setRepsCompound : $setRepsIsolation;

                    // Posición dentro del día (1-indexed) — 1=primer compound, último=isolation cierre.
                    $isFirst = $position === 0;
                    $isLast = $position === $totalEj - 1;

                    // Técnica de intensificación según fase + posición (sólo al cierre del grupo).
                    $tecnicaIntensificacion = $this->periodization->intensificationFor(
                        $weekMeta['fase'],
                        $position,
                        $totalEj,
                        $isCompound,
                    );

                    // Notas técnicas enriquecidas
                    $notes = $this->notesBuilder->buildFor($exercise);
                    $variation = $this->notesBuilder->resolveFirstVariation($exercise);

                    $ejercicioJson = [
                        'nombre' => $exercise->name_canonical,
                        'series' => $setReps['series'],
                        'repeticiones' => $setReps['reps'],
                        'descanso' => $setReps['descanso'],
                        'rir' => $weekMeta['rir'],
                        'gif_url' => $exercise->gifUrl(),
                        // ENRIQUECIMIENTO NUEVO:
                        'notas' => $notes['notas'],
                        'tecnica_ejecucion' => $notes['tecnica_ejecucion'],
                        'errores_comunes' => $notes['errores_comunes'],
                        'musculo_primario' => $exercise->muscle_primary,
                        'tipo' => $isCompound ? 'compound' : 'isolation',
                        'orden' => $position + 1,
                    ];

                    // Técnica de intensificación (drop set / superset / rest-pause / etc.)
                    if ($tecnicaIntensificacion !== null) {
                        $ejercicioJson['tecnica_intensificacion'] = $tecnicaIntensificacion;
                    }

                    // Variación alterna (clickeable en UI)
                    if ($variation !== null) {
                        $ejercicioJson['variacion'] = $variation;
                    }

                    $ejerciciosJson[] = $ejercicioJson;
                }

                $diasJson[] = [
                    'dia_semana' => $day->dayName,
                    'grupo_muscular' => $day->groupLabel,
                    'nombre' => "{$day->dayName} — {$day->groupLabel}",
                    'duracion_estimada_min' => $this->estimateDayDuration($ejercicios, $setRepsCompound, $setRepsIsolation),
                    'calentamiento' => $this->buildWarmupForDay($day),
                    'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
                    'ejercicios' => $ejerciciosJson,
                ];
            }

            $semanas[] = [
                'numero' => $weekNumber,
                'fase' => $faseLabel,
                'rir_objetivo' => $weekMeta['rir'],
                'volumen_pct' => $weekMeta['volumen_pct'],
                'descripcion' => $this->describeWeek($weekMeta['fase'], $weekNumber, $weekMeta['rir']),
                'dias' => $diasJson,
            ];
        }

        // Sprint 32: inyectar principles relevantes
        $injectedPrinciples = $this->principleInjector->selectTop($profile, 'entrenamiento', limit: 3);
        $extraTips = $this->principleInjector->asTipsArray($injectedPrinciples);

        $planJson = [
            'plan_type' => 'entrenamiento',
            'titulo' => $this->buildTitle($ctx),
            'objetivo' => $this->coachNotesBuilder->buildObjetivoEntrenamiento($profile, (string) $methodology->name),
            'metodologia' => (string) $methodology->name,
            'frecuencia' => count($splitDays) . ' dias/semana',
            'frecuencia_dias' => count($splitDays),
            'duracion_semanas' => self::DURACION_MENSUAL,
            'fecha_inicio' => $ctx->fechaInicio,
            'split' => $this->buildSplitMap($splitDays),
            'notas_coach' => $this->coachNotesBuilder->buildForEntrenamiento(
                $profile,
                $ctx->clientName,
                $ctx->coachName,
                (string) $methodology->name,
                self::DURACION_MENSUAL,
            ),
            'consejos_coach' => $this->buildConsejosCoach($profile),
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

    /**
     * Estima duración del día en minutos según número de ejercicios + series.
     * Compounds: ~6 min/ejercicio (3 series × 90s descanso + ejecución).
     * Isolations: ~4 min/ejercicio (3 series × 60-75s + ejecución).
     *
     * @param ExerciseMetadata[] $exercises
     */
    private function estimateDayDuration(array $exercises, array $setRepsCompound, array $setRepsIsolation): int
    {
        $total = 8; // 5 min warmup + 3 min vuelta a la calma
        foreach ($exercises as $exercise) {
            $isCompound = $exercise->compound_isolation === 'compound';
            $setReps = $isCompound ? $setRepsCompound : $setRepsIsolation;
            $restSeconds = (int) preg_replace('/[^0-9]/', '', $setReps['descanso']);
            $perSet = max($restSeconds, 60) + 45; // 45s ejecución promedio
            $total += (int) round(($setReps['series'] * $perSet) / 60);
        }
        return $total;
    }

    private function buildWarmupForDay(SplitDay $day): string
    {
        $muscles = $day->muscleTargets;
        $hasLower = ! empty(array_intersect($muscles, ['Cuadriceps', 'Femorales', 'Glúteos', 'Isquiotibiales', 'Gemelos', 'Pantorrillas']));
        $hasUpper = ! empty(array_intersect($muscles, ['Pecho', 'Espalda', 'Hombros', 'Bíceps', 'Tríceps', 'Dorsal']));

        if ($hasLower && ! $hasUpper) {
            return '5 min bici o caminadora suave + 2×10 sentadilla libre + 2×10 zancadas + 1×15 puente glúteo. Total: 8 min.';
        }
        if ($hasUpper && ! $hasLower) {
            return '5 min remo o caminadora + rotaciones de hombro 2×15 + 1×15 push-up rodillas + 1×10 face-pull con banda. Total: 8 min.';
        }
        if ($hasLower && $hasUpper) {
            return '5 min cardio suave + activación general: 2×10 sentadilla libre + 1×15 push-up rodillas + rotaciones hombro 2×15. Total: 10 min.';
        }
        return '5 min cardio suave + movilidad articular general. Total: 8 min.';
    }

    private function describeWeek(string $fase, int $weekNumber, int $rir): string
    {
        return match (true) {
            $weekNumber === 1 => "Semana de adaptación. RIR {$rir} — quedate con {$rir} reps en el tanque al terminar cada serie. Esta semana la técnica manda sobre el peso. Andá sintiendo cómo trabaja cada músculo.",
            $weekNumber === 2 => "Acá ya estás más enchufado. Si la técnica te sale sólida, subí peso — todavía te tienen que quedar {$rir} reps en el tanque al terminar la serie.",
            $weekNumber === 3 => "Esta semana le metés en serio. Pesos cercanos al tope, descanso completo entre series. RIR {$rir}. Es la semana más dura del mes — vas a sentirlo.",
            $weekNumber === 4 => "Última semana del bloque, dale todo lo que tenés con técnica intacta. RIR {$rir}. Si no llegás, mantené el peso y mejorá la ejecución.",
            default => "Semana {$weekNumber} — RIR {$rir}.",
        };
    }

    /**
     * Bullets accionables al coach (como las antiguas "CONSEJOS DE TU COACH").
     *
     * @return string[]
     */
    private function buildConsejosCoach(\App\Services\DecisionEngine\Data\ClientProfile $profile): array
    {
        $base = [
            'Calentá siempre 5-10 min antes de la primera serie',
            'Empezá por los ejercicios grandes (sentadilla, peso muerto, press) y dejá los de aislación al final',
            'Anotá peso y RIR de cada serie apenas terminás el ejercicio',
            'Si te duele una articulación (no fatiga muscular, dolor que pincha), parás y me escribís',
            'Tomá agua durante el entreno — mínimo 500 ml por hora',
            'Dormí entre 7 y 9 horas. El músculo se construye durmiendo, no en el gym',
        ];

        return match ($profile->goal) {
            'perdida_grasa' => array_merge($base, [
                'Si hacés cardio, va después de las pesas, no antes — no robés energía al trabajo de fuerza',
                'Si te baja la energía, una taza de café 30 min antes ayuda',
            ]),
            'hipertrofia' => array_merge($base, [
                'La última serie de cada ejercicio llegá al RIR que te pongo, no menos',
                'Apenas terminás el entreno, comé algo con proteína y carbohidrato en la primera hora',
            ]),
            'fuerza' => array_merge($base, [
                'Descanso completo entre series — 2-3 min en los ejercicios grandes',
                'Si no podés mantener la técnica, bajás peso. La técnica gana siempre',
            ]),
            default => $base,
        };
    }

    /**
     * @return string[]
     */
    private function buildTips(): array
    {
        return [
            'Anotá peso, reps y RIR de cada serie apenas terminás el ejercicio',
            'Tomá agua durante el entreno (mínimo 500 ml por hora)',
            'Dormí al menos 7 horas. El músculo se construye durmiendo, no en el gym',
            'Si te duele una articulación (no fatiga muscular, dolor que pincha), parás y me escribís',
        ];
    }
}
