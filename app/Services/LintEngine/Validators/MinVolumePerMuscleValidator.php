<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta planes con UNDERTRAINING — grupos musculares trabajados pero con muy
 * poco volumen semanal (<min_series_per_week).
 *
 * Opuesto a VolumeBalancePerMuscleValidator (que detecta DESEQUILIBRIO).
 * Aquí detectamos volumen total bajo: ej. plan que toca pecho solo 1 día con
 * 2 ejercicios x 3 series = 6 series/sem — muy poco para hipertrofia (min ~10).
 *
 * Referencia científica: Schoenfeld 2017 meta-análisis sugiere 10+ series/sem
 * por grupo es el mínimo viable para hipertrofia. Por debajo, las ganancias
 * son marginales aunque la técnica sea buena.
 *
 * Algoritmo:
 *   1. Para la primera semana, agrupa ejercicios por grupo canónico.
 *   2. Cuenta series totales por grupo.
 *   3. Para cada grupo TRABAJADO (>=1 ejercicio), si total < min_series_per_week,
 *      genera warning.
 *
 * Grupos "secundarios" (Core, Cardiovascular, Antebrazos) no aplican esta regla.
 */
final class MinVolumePerMuscleValidator extends BaseValidator
{
    /** Reutiliza el keyword mapping del VolumeBalance. */
    private const KEYWORD_TO_GROUP = [
        'pecho' => 'pecho',
        'tríceps' => 'triceps',
        'triceps' => 'triceps',
        'espalda' => 'espalda',
        'bíceps' => 'biceps',
        'biceps' => 'biceps',
        'cuádriceps' => 'cuadriceps',
        'cuadriceps' => 'cuadriceps',
        'cuad' => 'cuadriceps',
        'isquiotibial' => 'isquiotibiales',
        'femoral' => 'isquiotibiales',
        'posterior' => 'isquiotibiales',
        'glúteo' => 'gluteo',
        'gluteo' => 'gluteo',
        'hombro' => 'hombros',
    ];

    /** Grupos que SÍ aplican la regla (mayores). */
    private const MAJOR_GROUPS = ['pecho', 'espalda', 'cuadriceps', 'isquiotibiales', 'gluteo', 'hombros', 'biceps', 'triceps'];

    public function name(): string
    {
        return 'min_volume_per_muscle';
    }

    public function check(LintContext $ctx): array
    {
        $minSeries = (int) ($ctx->checkDefinition['min_series_per_week'] ?? 10);
        $semanas = $ctx->plan['semanas'] ?? [];

        if (! is_array($semanas) || $semanas === []) {
            return [];
        }

        $firstWeek = $semanas[0] ?? null;
        if (! is_array($firstWeek) || ! isset($firstWeek['dias'])) {
            return [];
        }

        // Series totales por grupo canónico
        $seriesByGroup = [];
        foreach ($firstWeek['dias'] as $dia) {
            $groupLabel = (string) ($dia['grupo_muscular'] ?? '');
            $canonical = $this->extractCanonicalGroups($groupLabel);
            if ($canonical === []) {
                continue;
            }
            $totalSeriesDay = 0;
            foreach (($dia['ejercicios'] ?? []) as $ej) {
                $totalSeriesDay += (int) ($ej['series'] ?? 0);
            }
            $perGroup = $totalSeriesDay / max(1, count($canonical));
            foreach ($canonical as $g) {
                $seriesByGroup[$g] = ($seriesByGroup[$g] ?? 0) + $perGroup;
            }
        }

        $violations = [];
        foreach ($seriesByGroup as $group => $totalSeries) {
            if (! in_array($group, self::MAJOR_GROUPS, true)) {
                continue;
            }
            if ($totalSeries < $minSeries) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    '$.semanas[0].dias[*]',
                    sprintf(
                        'Volumen insuficiente para %s: %.1f series/semana (mínimo %d para estímulo hipertrófico viable). Considerá agregar 1-2 ejercicios más para ese grupo.',
                        $group, $totalSeries, $minSeries,
                    ),
                    [
                        'grupo' => $group,
                        'series_actual' => round($totalSeries, 1),
                        'minimo_recomendado' => $minSeries,
                    ],
                );
            }
        }

        return $violations;
    }

    /**
     * @return string[]
     */
    private function extractCanonicalGroups(string $label): array
    {
        $labelLower = mb_strtolower($label);
        $detected = [];
        foreach (self::KEYWORD_TO_GROUP as $keyword => $canonical) {
            if (str_contains($labelLower, $keyword) && ! in_array($canonical, $detected, true)) {
                $detected[] = $canonical;
            }
        }
        return $detected;
    }
}
