<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Splits;

use App\Services\ComposeEngine\Data\SplitDay;
use RuntimeException;

/**
 * Construye el array de SplitDay para una metodología dada.
 *
 * Cada metodología tiene un split canónico. Si el cliente especifica gender=F
 * con goal hipertrofia o recomposicion, ajustamos el énfasis hacia glúteo/pierna
 * (Lunes glúteo+cardio, Viernes glúteo+femoral) — patrón WellCore observado.
 */
final class SplitBuilder
{
    /**
     * Mapa día → muscle_primary keys del catálogo exercise_metadata. Sirve para
     * traducir un split-override del coach (ej. 'gluteo+femoral') a los targets
     * que el ExerciseSelector entiende.
     */
    private const KEYWORD_TO_MUSCLE = [
        // Tren inferior
        'gluteo' => ['Glúteo'],
        'glúteo' => ['Glúteo'],
        'gluteos' => ['Glúteo'],
        'cuadriceps' => ['Cuádriceps'],
        'cuádriceps' => ['Cuádriceps'],
        'cuad' => ['Cuádriceps'],
        'pierna' => ['Cuádriceps', 'Glúteo'],
        'piernas' => ['Cuádriceps', 'Glúteo'],
        'femoral' => ['Isquiotibiales'],
        'isquio' => ['Isquiotibiales'],
        'isquiotibiales' => ['Isquiotibiales'],
        'pantorrilla' => ['Gemelos'],
        'gemelos' => ['Gemelos'],
        'aductor' => ['Aductores'],
        // Tren superior
        'pecho' => ['Pecho'],
        'espalda' => ['Espalda'],
        'dorsales' => ['Espalda'],
        'hombro' => ['Hombros'],
        'hombros' => ['Hombros'],
        'biceps' => ['Bíceps'],
        'bíceps' => ['Bíceps'],
        'triceps' => ['Tríceps'],
        'tríceps' => ['Tríceps'],
        'brazos' => ['Bíceps', 'Tríceps'],
        // Core
        'abs' => ['Core'],
        'abdominal' => ['Core'],
        'abdominales' => ['Core'],
        'core' => ['Core'],
    ];

    /**
     * @param array<string,string> $splitOverride Mapa día (lunes|martes|...) → grupo (gluteo+femoral)
     * @return SplitDay[]
     */
    public function build(
        string $methodologySlug,
        ?string $gender = null,
        ?string $goal = null,
        array $splitOverride = [],
    ): array {
        if ($splitOverride !== []) {
            return $this->fromOverride($splitOverride);
        }

        return match ($methodologySlug) {
            'body_part_split_5d' => $this->bodyPartSplit5d($gender, $goal),
            'upper_lower_4d' => $this->upperLower4d(),
            'ppl_6d' => $this->ppl6d(),
            default => throw new RuntimeException(
                "SplitBuilder: methodology '$methodologySlug' aún no tiene split definido. " .
                'Definir en SplitBuilder antes de componer.'
            ),
        };
    }

    /**
     * Traduce override del coach a SplitDay[].
     * Input: ['lunes' => 'gluteo+cardio', 'martes' => 'hombro+triceps+abs']
     * Output: SplitDay[] con muscleTargets resueltos via KEYWORD_TO_MUSCLE.
     *
     * Keywords desconocidos se ignoran (con warning loggeado en el groupLabel).
     *
     * @param array<string,string> $override
     * @return SplitDay[]
     */
    private function fromOverride(array $override): array
    {
        $dayNamesMap = [
            'lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles',
            'miércoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes',
            'sabado' => 'Sábado', 'sábado' => 'Sábado', 'domingo' => 'Domingo',
        ];
        $days = [];
        foreach ($override as $dayKey => $groupRaw) {
            $dayLabel = $dayNamesMap[mb_strtolower($dayKey)] ?? ucfirst($dayKey);
            $keywords = preg_split('/[+\\s,]+/', mb_strtolower((string) $groupRaw)) ?: [];
            $targets = [];
            $labelParts = [];
            foreach ($keywords as $kw) {
                $kw = trim($kw);
                if ($kw === '' || $kw === 'cardio' || str_starts_with($kw, 'descanso')) {
                    if ($kw === 'cardio') {
                        $labelParts[] = 'Cardio';
                    }
                    continue;
                }
                $resolved = self::KEYWORD_TO_MUSCLE[$kw] ?? null;
                if ($resolved !== null) {
                    foreach ($resolved as $t) {
                        if (! in_array($t, $targets, true)) {
                            $targets[] = $t;
                        }
                    }
                    $labelParts[] = ucfirst($kw);
                }
            }
            if ($targets === []) {
                continue; // día sin musculatura válida — se omite
            }
            $days[] = new SplitDay(
                dayName: $dayLabel,
                groupLabel: implode(' + ', $labelParts),
                muscleTargets: $targets,
            );
        }
        return $days;
    }

    /**
     * Body Part Split 5d — distribución canónica con sesgo glúteo si femenino/recomp.
     */
    private function bodyPartSplit5d(?string $gender, ?string $goal): array
    {
        $emphasizeGlute = $gender === 'femenino' || $gender === 'F'
            || in_array($goal, ['perdida_grasa', 'recomposicion'], true);

        if ($emphasizeGlute) {
            return [
                new SplitDay('Lunes', 'Glúteo + Cardio', ['Glúteo']),
                new SplitDay('Martes', 'Hombro + Tríceps + Core', ['Hombros', 'Tríceps', 'Core']),
                new SplitDay('Miércoles', 'Cuádriceps + Gemelos', ['Cuádriceps', 'Isquiotibiales']),
                new SplitDay('Jueves', 'Espalda + Bíceps + Core', ['Espalda', 'Bíceps', 'Core']),
                new SplitDay('Viernes', 'Glúteo + Femoral', ['Glúteo', 'Isquiotibiales']),
            ];
        }

        return [
            new SplitDay('Lunes', 'Pecho + Tríceps', ['Pecho', 'Tríceps']),
            new SplitDay('Martes', 'Espalda + Bíceps', ['Espalda', 'Bíceps']),
            new SplitDay('Miércoles', 'Pierna (Cuádriceps)', ['Cuádriceps', 'Glúteo']),
            new SplitDay('Jueves', 'Hombros + Core', ['Hombros', 'Core']),
            new SplitDay('Viernes', 'Pierna posterior + Glúteo', ['Isquiotibiales', 'Glúteo']),
        ];
    }

    private function upperLower4d(): array
    {
        return [
            new SplitDay('Lunes', 'Upper A — Pecho + Espalda + Brazos', ['Pecho', 'Espalda', 'Tríceps']),
            new SplitDay('Martes', 'Lower A — Cuádriceps + Glúteo', ['Cuádriceps', 'Glúteo']),
            new SplitDay('Jueves', 'Upper B — Hombros + Espalda + Brazos', ['Hombros', 'Espalda', 'Bíceps']),
            new SplitDay('Viernes', 'Lower B — Posterior + Glúteo', ['Isquiotibiales', 'Glúteo']),
        ];
    }

    private function ppl6d(): array
    {
        return [
            new SplitDay('Lunes', 'Push A — Pecho + Hombro + Tríceps', ['Pecho', 'Hombros', 'Tríceps']),
            new SplitDay('Martes', 'Pull A — Espalda + Bíceps', ['Espalda', 'Bíceps']),
            new SplitDay('Miércoles', 'Legs A — Cuádriceps + Glúteo', ['Cuádriceps', 'Glúteo']),
            new SplitDay('Jueves', 'Push B — Pecho + Hombro + Tríceps', ['Pecho', 'Hombros', 'Tríceps']),
            new SplitDay('Viernes', 'Pull B — Espalda + Bíceps', ['Espalda', 'Bíceps']),
            new SplitDay('Sábado', 'Legs B — Posterior + Glúteo', ['Isquiotibiales', 'Glúteo']),
        ];
    }
}
