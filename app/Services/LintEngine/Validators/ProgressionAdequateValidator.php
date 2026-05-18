<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta planes con progresión INVERTIDA (RIR sube semana a semana).
 *
 * Principio fisiológico: en periodización lineal estándar, RIR debe bajar o
 * mantenerse semana a semana (= más esfuerzo, más intensidad). Si RIR sube,
 * el plan está pidiendo MENOS esfuerzo en semanas posteriores, lo cual rompe
 * la lógica de sobrecarga progresiva.
 *
 * Excepción: Deload (RIR alto intencional) o Recuperación entre fases pesadas.
 * El validator considera el contexto: si la fase es "Deload"/"Recuperación",
 * el RIR alto NO cuenta como violación.
 *
 * Algoritmo:
 *   1. Para cada par de semanas consecutivas (i, i+1), calcula el RIR promedio
 *      de los ejercicios.
 *   2. Si rir[i+1] > rir[i] + tolerance Y semana[i+1].fase != Deload/Recuperación:
 *      genera violation.
 *
 * Usado por: heur_progression_inverted.
 */
final class ProgressionAdequateValidator extends BaseValidator
{
    private const DELOAD_FASES = ['Deload', 'Recuperación', 'Recuperacion'];

    public function name(): string
    {
        return 'progression_adequate';
    }

    public function check(LintContext $ctx): array
    {
        $tolerance = (float) ($ctx->checkDefinition['tolerance'] ?? 0.5);
        $semanas = $ctx->plan['semanas'] ?? [];

        if (! is_array($semanas) || count($semanas) < 2) {
            return [];
        }

        // RIR promedio por semana + fase label
        $weekData = [];
        foreach ($semanas as $i => $sem) {
            if (! is_array($sem)) {
                continue;
            }
            $faseLabel = $this->extractFaseLabel($sem);
            $avgRir = $this->avgRir($sem);
            $weekData[] = [
                'index' => $i,
                'fase_label' => $faseLabel,
                'avg_rir' => $avgRir,
            ];
        }

        $violations = [];
        for ($i = 0; $i < count($weekData) - 1; $i++) {
            $current = $weekData[$i];
            $next = $weekData[$i + 1];

            // Si la siguiente semana es Deload/Recuperación, el RIR alto es esperado.
            if ($this->isDeload($next['fase_label'])) {
                continue;
            }

            // Si no tenemos datos de RIR válidos en ambas semanas, skip.
            if ($current['avg_rir'] === null || $next['avg_rir'] === null) {
                continue;
            }

            if ($next['avg_rir'] > $current['avg_rir'] + $tolerance) {
                $weekNum1 = ($current['index'] + 1);
                $weekNum2 = ($next['index'] + 1);
                $violations[] = $this->makeViolation(
                    $ctx,
                    "\$.semanas[{$next['index']}]",
                    sprintf(
                        "Progresión invertida detectada: semana %d (%s) tiene RIR promedio %.1f mayor que semana %d (%s) con RIR %.1f. En periodización lineal, RIR debe bajar o mantenerse — subir RIR = menos esfuerzo posterior, rompe sobrecarga progresiva.",
                        $weekNum2, $next['fase_label'] ?: '?',
                        $next['avg_rir'],
                        $weekNum1, $current['fase_label'] ?: '?',
                        $current['avg_rir'],
                    ),
                    [
                        'week_from' => $weekNum1,
                        'fase_from' => $current['fase_label'],
                        'rir_from' => round($current['avg_rir'], 2),
                        'week_to' => $weekNum2,
                        'fase_to' => $next['fase_label'],
                        'rir_to' => round($next['avg_rir'], 2),
                    ],
                );
            }
        }

        return $violations;
    }

    private function extractFaseLabel(array $semana): string
    {
        // Puede venir en 'fase' (formato "Adaptación · RIR 3") o 'fase_nombre'.
        $raw = (string) ($semana['fase'] ?? $semana['fase_nombre'] ?? '');
        // Tomar solo la primera palabra antes de "·" o " RIR" para identificar el nombre canónico.
        if (str_contains($raw, '·')) {
            $raw = trim(explode('·', $raw)[0]);
        }
        return trim($raw);
    }

    private function isDeload(string $faseLabel): bool
    {
        foreach (self::DELOAD_FASES as $deload) {
            if (stripos($faseLabel, $deload) !== false) {
                return true;
            }
        }
        return false;
    }

    private function avgRir(array $semana): ?float
    {
        $rirs = [];
        foreach (($semana['dias'] ?? []) as $dia) {
            foreach (($dia['ejercicios'] ?? []) as $ej) {
                if (! isset($ej['rir'])) {
                    continue;
                }
                $rir = $ej['rir'];
                if (is_numeric($rir)) {
                    $rirs[] = (float) $rir;
                }
            }
        }
        if ($rirs === []) {
            return null;
        }
        return array_sum($rirs) / count($rirs);
    }
}
