<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Heurística anti-monotonía: detecta planes donde >threshold_pct% de ejercicios
 * usan la misma combinación series/reps. Ej. plan donde todo es 3×12 o 4×10.
 *
 * Usado por: heur_monotonia_3x12.
 */
final class PercentageSameSetsRepsValidator extends BaseValidator
{
    public function name(): string
    {
        return 'percentage_same_sets_reps';
    }

    public function check(LintContext $ctx): array
    {
        $threshold = (float) ($ctx->checkDefinition['threshold_pct'] ?? 60);
        $patterns = $ctx->checkDefinition['patterns'] ?? [];

        if (! is_array($patterns) || $patterns === []) {
            return [];
        }

        // Recolectar todos los ejercicios del plan
        $exercises = $this->collectExercises($ctx->plan);
        $total = count($exercises);
        if ($total === 0) {
            return [];
        }

        // Contar cuántos matchean cada pattern
        $matches = 0;
        foreach ($exercises as $ex) {
            foreach ($patterns as $pattern) {
                $expectedSeries = $pattern['series'] ?? null;
                $expectedReps = $pattern['reps'] ?? null;
                $actualSeries = $ex['series'] ?? null;
                $actualReps = $ex['repeticiones'] ?? $ex['reps'] ?? null;

                if ($expectedSeries !== null && (int) $actualSeries !== (int) $expectedSeries) {
                    continue;
                }
                if ($expectedReps !== null && (string) $actualReps !== (string) $expectedReps) {
                    continue;
                }
                $matches++;
                break;
            }
        }

        $pct = ($matches / $total) * 100;
        if ($pct > $threshold) {
            return [$this->makeViolation(
                $ctx,
                '$.semanas[*].dias[*].ejercicios[*]',
                sprintf(
                    "Monotonía detectada: %.0f%% de %d ejercicios usan la misma combinación series/reps (threshold %.0f%%).",
                    $pct,
                    $total,
                    $threshold,
                ),
                ['matches' => $matches, 'total' => $total, 'pct' => round($pct, 1)],
            )];
        }

        return [];
    }

    private function collectExercises(array $plan): array
    {
        $out = [];
        $semanas = $plan['semanas'] ?? [];
        if (! is_array($semanas)) {
            return $out;
        }
        foreach ($semanas as $semana) {
            $dias = $semana['dias'] ?? [];
            if (! is_array($dias)) continue;
            foreach ($dias as $dia) {
                $ejercicios = $dia['ejercicios'] ?? [];
                if (! is_array($ejercicios)) continue;
                foreach ($ejercicios as $ej) {
                    if (is_array($ej)) {
                        $out[] = $ej;
                    }
                }
            }
        }
        return $out;
    }
}
