<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Verifica que el plan incluya AL MENOS un ejercicio unilateral por semana (no solo bilaterales).
 *
 * Razón: trabajar solo bilateral (sentadilla con barra, press de banca, etc.)
 * permite que el lado fuerte compense al débil → asimetría crónica → riesgo de lesión.
 * Unilaterales (zancada, búlgara, press unilateral, remo unilateral) fuerzan
 * trabajo equilibrado.
 *
 * Solo aplica a plan_type=entrenamiento.
 *
 * Detección:
 *   - keywords unilaterales en `ejercicios[].nombre`: zancada, búlgara, unilateral,
 *     single-leg, paso, dumbbell row (unilateral implícito), press alterno,
 *     pistol squat, peso muerto pierna, plancha lateral.
 *   - Si encuentra ≥1 en la semana → OK.
 *   - Si todos los ejercicios son bilaterales → warning.
 *
 * Usado por: heur_unilateral_balance.
 */
final class UnilateralBalanceValidator extends BaseValidator
{
    private const KEYWORDS = [
        'unilateral',
        'zancada', 'lunge', 'lunges',
        'búlgara', 'bulgara', 'bulgarian',
        'split squat',
        'single-leg', 'single leg', 'una pierna',
        'paso adelante', 'step-up', 'step up',
        'press alterno', 'remo alterno', 'curl alterno',
        'pistol squat',
        'peso muerto rumano una pierna', 'rdl una pierna',
        'plancha lateral', 'side plank',
        'turkish get-up', 'turkish getup',
    ];

    public function name(): string
    {
        return 'unilateral_balance';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;

        if (($plan['plan_type'] ?? null) !== 'entrenamiento') {
            return [];
        }

        $semanas = $plan['semanas'] ?? [];
        if (! is_array($semanas) || $semanas === []) {
            return [];
        }

        // Solo evaluamos semana 1 (asumimos similar a las demás)
        $semana = $semanas[0];
        $allNames = [];
        foreach (($semana['dias'] ?? []) as $dia) {
            foreach (($dia['ejercicios'] ?? []) as $ej) {
                $name = mb_strtolower((string) ($ej['nombre'] ?? ''));
                if ($name !== '') {
                    $allNames[] = $name;
                }
            }
        }

        if ($allNames === []) {
            return [];
        }

        foreach ($allNames as $name) {
            foreach (self::KEYWORDS as $kw) {
                if (str_contains($name, $kw)) {
                    return [];
                }
            }
        }

        return [$this->makeViolation(
            $ctx,
            '$.semanas[0].dias[*].ejercicios',
            'Plan de entrenamiento sin ejercicios unilaterales en la semana. Trabajar solo bilateral permite que el lado fuerte compense al débil → asimetría crónica. Agregar al menos 1 unilateral (zancada, búlgara, press alterno, etc.).',
            [
                'ejercicios_examinados' => count($allNames),
                'sugerencias' => ['Zancada caminando', 'Sentadilla búlgara', 'Press de hombros unilateral', 'Remo unilateral con mancuerna', 'Paso adelante (step-up)'],
            ],
        )];
    }
}
