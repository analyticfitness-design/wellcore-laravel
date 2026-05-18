<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta desbalance de volumen entre grupos musculares en una semana.
 *
 * Algoritmo:
 *   1. Para la primera semana del plan, agrupa ejercicios por `grupo_muscular`
 *      del día (mapea a un grupo canónico vía keyword matching).
 *   2. Cuenta total de SERIES (no ejercicios) por grupo en la semana.
 *   3. Compara opuestos clásicos:
 *      - Pecho vs Espalda
 *      - Cuádriceps vs Isquiotibiales
 *      - Bíceps vs Tríceps
 *   4. Si el ratio max/min > threshold (default 2.0), produce warning.
 *
 * Usado por: heur_volume_imbalance.
 *
 * Razonamiento fisiológico: desbalance >2× entre antagonistas genera adaptaciones
 * asimétricas (postura, lesiones, plateau). Estándar científico: ratio 0.8-1.2
 * para principales antagonistas, máx 1.5 antes de considerar específico.
 */
final class VolumeBalancePerMuscleValidator extends BaseValidator
{
    /** Palabras clave → grupo canónico para parsing del label "grupo_muscular" del día. */
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
        'core' => 'core',
        'abdom' => 'core',
        'gemelo' => 'pantorrilla',
        'pantorrilla' => 'pantorrilla',
    ];

    /** Pares antagonistas que esperamos balanceados. */
    private const ANTAGONIST_PAIRS = [
        ['pecho', 'espalda'],
        ['cuadriceps', 'isquiotibiales'],
        ['biceps', 'triceps'],
    ];

    public function name(): string
    {
        return 'volume_balance_per_muscle';
    }

    public function check(LintContext $ctx): array
    {
        $threshold = (float) ($ctx->checkDefinition['max_ratio'] ?? 2.0);
        $minSeriesToCount = (int) ($ctx->checkDefinition['min_series_per_group'] ?? 4);

        $semanas = $ctx->plan['semanas'] ?? [];
        if (! is_array($semanas) || $semanas === []) {
            return [];
        }

        $firstWeek = $semanas[0] ?? null;
        if (! is_array($firstWeek) || ! isset($firstWeek['dias'])) {
            return [];
        }

        // Contar series totales por grupo canónico en la primera semana
        $seriesByGroup = [];
        foreach ($firstWeek['dias'] as $dia) {
            $groupLabel = (string) ($dia['grupo_muscular'] ?? '');
            $canonicalGroups = $this->extractCanonicalGroups($groupLabel);
            if ($canonicalGroups === []) {
                continue;
            }

            $totalSeriesDay = 0;
            foreach (($dia['ejercicios'] ?? []) as $ej) {
                $totalSeriesDay += (int) ($ej['series'] ?? 0);
            }

            // Si el día menciona N grupos, distribuir las series proporcionalmente.
            // Aproximación simple: dividir parejo entre los grupos del día.
            $perGroup = $totalSeriesDay / max(1, count($canonicalGroups));
            foreach ($canonicalGroups as $g) {
                $seriesByGroup[$g] = ($seriesByGroup[$g] ?? 0) + $perGroup;
            }
        }

        $violations = [];
        foreach (self::ANTAGONIST_PAIRS as [$a, $b]) {
            $sA = $seriesByGroup[$a] ?? 0;
            $sB = $seriesByGroup[$b] ?? 0;

            // Si ninguno tiene volumen significativo, skip (no aplica).
            if ($sA < $minSeriesToCount && $sB < $minSeriesToCount) {
                continue;
            }

            $max = max($sA, $sB);
            $min = max(1, min($sA, $sB)); // evitar div by zero
            $ratio = $max / $min;

            if ($ratio > $threshold) {
                $dominantGroup = $sA > $sB ? $a : $b;
                $subordinateGroup = $sA > $sB ? $b : $a;
                $violations[] = $this->makeViolation(
                    $ctx,
                    '$.semanas[0].dias[*]',
                    sprintf(
                        "Desbalance de volumen detectado: %s (%.1f series/sem) vs %s (%.1f series/sem) — ratio %.2f (threshold %.1f). Considera aumentar volumen del antagonista o reducir el dominante.",
                        $dominantGroup, max($sA, $sB),
                        $subordinateGroup, min($sA, $sB),
                        $ratio, $threshold,
                    ),
                    [
                        'dominant' => $dominantGroup,
                        'dominant_series' => max($sA, $sB),
                        'subordinate' => $subordinateGroup,
                        'subordinate_series' => min($sA, $sB),
                        'ratio' => round($ratio, 2),
                        'threshold' => $threshold,
                    ],
                );
            }
        }

        return $violations;
    }

    /**
     * @return string[] grupos canónicos detectados en el label.
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
