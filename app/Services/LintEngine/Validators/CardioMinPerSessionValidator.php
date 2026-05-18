<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta sesiones con cardio excesivo en fases que privilegian fuerza/hipertrofia.
 *
 * Usado por: heur_cardio_excessive.
 * Si una semana está en fase X y la suma de minutos de cardio del día excede
 * max_minutes_in_phase[X], se reporta violation.
 */
final class CardioMinPerSessionValidator extends BaseValidator
{
    public function name(): string
    {
        return 'cardio_min_per_session';
    }

    public function check(LintContext $ctx): array
    {
        $maxByPhase = $ctx->checkDefinition['max_minutes_in_phase'] ?? [];
        if (! is_array($maxByPhase) || $maxByPhase === []) {
            return [];
        }

        $semanas = $ctx->plan['semanas'] ?? [];
        if (! is_array($semanas)) {
            return [];
        }

        $violations = [];
        foreach ($semanas as $sIdx => $semana) {
            if (! is_array($semana)) continue;
            $fase = $this->extractPhasePrefix($semana['fase'] ?? '');
            if (! isset($maxByPhase[$fase])) continue;
            $maxMin = (int) $maxByPhase[$fase];

            $dias = $semana['dias'] ?? [];
            if (! is_array($dias)) continue;
            foreach ($dias as $dIdx => $dia) {
                if (! is_array($dia)) continue;
                $cardioMin = $this->sumCardioMinutes($dia);
                if ($cardioMin > $maxMin) {
                    $violations[] = $this->makeViolation(
                        $ctx,
                        "$.semanas[$sIdx].dias[$dIdx]",
                        sprintf(
                            "Fase '%s' permite máximo %d min cardio/sesión, este día tiene %d min.",
                            $fase,
                            $maxMin,
                            $cardioMin,
                        ),
                        ['fase' => $fase, 'cardio_min' => $cardioMin, 'max_allowed' => $maxMin],
                    );
                }
            }
        }
        return $violations;
    }

    private function extractPhasePrefix(string $fase): string
    {
        $segments = explode(' · ', $fase, 2);
        return trim($segments[0]);
    }

    private function sumCardioMinutes(array $dia): int
    {
        $total = 0;

        // Cardio inline en cada día
        $cardio = $dia['cardio'] ?? null;
        if (is_array($cardio)) {
            $total += (int) ($cardio['duracion_min'] ?? 0);
            // Algunas variantes usan duracion: "30 min"
            if (! isset($cardio['duracion_min']) && isset($cardio['duracion'])) {
                $total += $this->parseMinutes((string) $cardio['duracion']);
            }
        }

        // Ejercicios con is_cardio:true y campo reps con minutos ej "30 min"
        $ejercicios = $dia['ejercicios'] ?? [];
        if (is_array($ejercicios)) {
            foreach ($ejercicios as $ej) {
                if (! is_array($ej)) continue;
                if (($ej['is_cardio'] ?? false) !== true) continue;
                $reps = $ej['repeticiones'] ?? $ej['reps'] ?? '';
                $duracionMin = $ej['duracion_min'] ?? null;
                if (is_int($duracionMin)) {
                    $total += $duracionMin;
                } elseif (is_string($reps)) {
                    $total += $this->parseMinutes($reps);
                }
            }
        }

        return $total;
    }

    private function parseMinutes(string $value): int
    {
        if (preg_match('/(\d+)\s*min/i', $value, $m) === 1) {
            return (int) $m[1];
        }
        return 0;
    }
}
