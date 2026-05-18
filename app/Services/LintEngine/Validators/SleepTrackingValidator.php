<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta planes de hábitos SIN tracking explícito de sueño.
 *
 * Razón: el sueño es el predictor #1 de recuperación, performance y composición.
 * Un plan de habitos serio debe incluir tracking de horas + calidad. Sin esto,
 * el coach no detecta el cuello de botella más común en clientes estancados.
 *
 * Solo aplica a plan_type=habitos.
 *
 * Detección: busca al menos un habit con categoria='sueno' o keyword
 * (sueño, sueno, dormir, descanso nocturno, horas de sueño) en nombre/objetivo.
 *
 * Usado por: heur_sleep_tracking_missing.
 */
final class SleepTrackingValidator extends BaseValidator
{
    private const KEYWORDS = ['sueno', 'sueño', 'dormir', 'descanso nocturno', 'horas de sueno', 'horas de sueño', 'sleep'];

    public function name(): string
    {
        return 'sleep_tracking';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;

        if (($plan['plan_type'] ?? null) !== 'habitos') {
            return [];
        }

        $habitos = $plan['habitos'] ?? [];
        if (! is_array($habitos)) {
            return [];
        }

        foreach ($habitos as $h) {
            $cat = mb_strtolower((string) ($h['categoria'] ?? ''));
            if ($cat === 'sueno' || $cat === 'sueño' || $cat === 'sleep') {
                return [];
            }
            $haystack = mb_strtolower(implode(' ', array_filter([
                (string) ($h['nombre'] ?? ''),
                (string) ($h['objetivo'] ?? ''),
                (string) ($h['tracking_method'] ?? ''),
            ])));
            foreach (self::KEYWORDS as $kw) {
                if (str_contains($haystack, $kw)) {
                    return [];
                }
            }
        }

        return [$this->makeViolation(
            $ctx,
            '$.habitos',
            'Plan de hábitos SIN tracking de sueño. El sueño es predictor #1 de recovery y composición. Agregar al menos 1 habit con categoria=sueno (horas + calidad).',
            [
                'habitos_count' => count($habitos),
                'recomendacion' => 'Habit canónico: nombre="Sueño 7-9h", categoria="sueno", tracking_method="diario en app o journal".',
            ],
        )];
    }
}
