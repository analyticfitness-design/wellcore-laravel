<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Models\Kb\ExerciseMetadata;
use App\Services\LintEngine\Data\LintContext;
use Throwable;

/**
 * Detecta desbalance push (empuje) vs pull (jalón) en el plan semanal.
 *
 * Razón fisiológica: el cuerpo necesita equilibrio entre empuje y jalón para
 * mantener postura sana. Una semana con 30 series de push vs 8 de pull genera
 * "síndrome cruzado superior" (hombros adelantados, cabeza hacia adelante,
 * desbalance retracción escapular).
 *
 * Estándar: ratio cercano a 1:1, máximo 1.5:1 antes de revisar.
 *
 * Algoritmo:
 *   1. Recoge ejercicios de la primera semana del plan.
 *   2. Para cada ejercicio, consulta exercise_metadata.movement_pattern.
 *   3. Agrupa series totales por:
 *      - push (push_horizontal + push_vertical)
 *      - pull (pull_horizontal + pull_vertical)
 *   4. Si max/min > threshold (default 1.5), genera warning.
 *
 * Usado por: heur_push_pull_imbalance.
 */
final class PushPullBalanceValidator extends BaseValidator
{
    private const PUSH_PATTERNS = ['push_horizontal', 'push_vertical'];
    private const PULL_PATTERNS = ['pull_horizontal', 'pull_vertical'];

    /** Cache en memoria: name_canonical → movement_pattern (evita N queries). */
    private array $patternCache = [];

    public function name(): string
    {
        return 'push_pull_balance';
    }

    public function check(LintContext $ctx): array
    {
        $threshold = (float) ($ctx->checkDefinition['max_ratio'] ?? 1.5);
        $minSeries = (int) ($ctx->checkDefinition['min_series'] ?? 8);

        $semanas = $ctx->plan['semanas'] ?? [];
        if (! is_array($semanas) || $semanas === []) {
            return [];
        }

        $firstWeek = $semanas[0] ?? null;
        if (! is_array($firstWeek) || ! isset($firstWeek['dias'])) {
            return [];
        }

        $this->preloadPatternCache();

        $pushSeries = 0.0;
        $pullSeries = 0.0;
        foreach ($firstWeek['dias'] as $dia) {
            foreach (($dia['ejercicios'] ?? []) as $ej) {
                $name = (string) ($ej['nombre'] ?? '');
                $series = (float) ($ej['series'] ?? 0);
                $pattern = $this->patternCache[$name] ?? null;
                if ($pattern === null) {
                    continue;
                }
                if (in_array($pattern, self::PUSH_PATTERNS, true)) {
                    $pushSeries += $series;
                } elseif (in_array($pattern, self::PULL_PATTERNS, true)) {
                    $pullSeries += $series;
                }
            }
        }

        if ($pushSeries < $minSeries && $pullSeries < $minSeries) {
            return [];
        }

        $max = max($pushSeries, $pullSeries);
        $min = max(1.0, min($pushSeries, $pullSeries));
        $ratio = $max / $min;

        if ($ratio > $threshold) {
            $dominante = $pushSeries > $pullSeries ? 'push' : 'pull';
            $subordinado = $dominante === 'push' ? 'pull' : 'push';
            return [$this->makeViolation(
                $ctx,
                '$.semanas[0].dias[*]',
                sprintf(
                    'Desbalance push/pull detectado: %s (%.1f series/sem) vs %s (%.1f series/sem) — ratio %.2f (threshold %.1f). Riesgo de síndrome cruzado superior y problemas posturales.',
                    $dominante, max($pushSeries, $pullSeries),
                    $subordinado, min($pushSeries, $pullSeries),
                    $ratio, $threshold,
                ),
                [
                    'dominante' => $dominante,
                    'push_series' => round($pushSeries, 1),
                    'pull_series' => round($pullSeries, 1),
                    'ratio' => round($ratio, 2),
                    'threshold' => $threshold,
                ],
            )];
        }

        return [];
    }

    private function preloadPatternCache(): void
    {
        if ($this->patternCache !== []) {
            return;
        }
        try {
            $rows = ExerciseMetadata::query()
                ->whereNotNull('movement_pattern')
                ->get(['name_canonical', 'movement_pattern']);
            foreach ($rows as $row) {
                $this->patternCache[(string) $row->name_canonical] = (string) $row->movement_pattern;
            }
        } catch (Throwable) {
            // Sin cache, validator skip silencioso
        }
    }
}
