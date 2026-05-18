<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta planes de entrenamiento SIN día de descanso explícito en la semana.
 *
 * Razón fisiológica: 7 días seguidos de entreno = overtraining garantizado en
 * mayoría de niveles (excepto atletas profesionales con periodización avanzada).
 * Minimum 1 día completo de descanso permite supercompensación.
 *
 * Detección:
 *   1. Solo aplica a plan_type=entrenamiento.
 *   2. Lee `split{}` top-level (Lunes/Martes/.../Domingo).
 *   3. Si TODOS los 7 días tienen entreno (split[dia] no vacío ni "Descanso"/"Off") → warning.
 *   4. Acepta también semanas[0].dias[*].tipo='descanso' como rest day.
 *
 * Usado por: heur_rest_day_missing.
 */
final class RestDayValidator extends BaseValidator
{
    private const REST_KEYWORDS = ['descanso', 'off', 'rest', 'libre', 'cardio suave', 'movilidad', 'mobility'];

    public function name(): string
    {
        return 'rest_day_missing';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;

        if (($plan['plan_type'] ?? null) !== 'entrenamiento') {
            return [];
        }

        // Check 1: split top-level
        $split = $plan['split'] ?? [];
        if (is_array($split) && count($split) >= 7) {
            $hasRest = false;
            foreach ($split as $dia => $contenido) {
                $val = mb_strtolower(trim((string) $contenido));
                if ($val === '' || $this->matchesRest($val)) {
                    $hasRest = true;
                    break;
                }
            }
            if ($hasRest) {
                return [];
            }
        }

        // Check 2: semanas[0].dias[*].tipo
        $semanas = $plan['semanas'] ?? [];
        if (is_array($semanas) && isset($semanas[0]['dias']) && is_array($semanas[0]['dias'])) {
            $dias = $semanas[0]['dias'];
            if (count($dias) >= 7) {
                foreach ($dias as $d) {
                    $tipo = mb_strtolower((string) ($d['tipo'] ?? ''));
                    if ($tipo === 'descanso' || $this->matchesRest($tipo)) {
                        return [];
                    }
                    // Sin ejercicios = descanso implícito
                    if (empty($d['ejercicios'])) {
                        return [];
                    }
                }
            } else {
                // <7 días = ya hay implícito (no es plan 7d straight)
                return [];
            }
        } else {
            // sin estructura semanas tampoco indica 7d straight
            return [];
        }

        return [$this->makeViolation(
            $ctx,
            '$.split',
            'Plan de entrenamiento SIN día de descanso explícito (7 días seguidos con carga). Riesgo de overtraining + adherencia baja. Marcar al menos 1 día como "Descanso" o "Cardio suave/movilidad".',
            [
                'dias_con_carga' => 7,
                'recomendacion' => 'Cambiar 1 día (típicamente Domingo) a "Descanso" o "Movilidad + caminata".',
            ],
        )];
    }

    private function matchesRest(string $text): bool
    {
        foreach (self::REST_KEYWORDS as $kw) {
            if (str_contains($text, $kw)) {
                return true;
            }
        }
        return false;
    }
}
