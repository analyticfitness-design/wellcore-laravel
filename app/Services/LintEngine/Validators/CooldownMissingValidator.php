<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta planes de entrenamiento sin mención explícita de cooldown / vuelta a la calma.
 *
 * Razón fisiológica: la vuelta a la calma facilita el retorno de la FC basal,
 * remueve metabolitos (lactato), reduce DOMS percibido, y baja el sistema simpático.
 * No tiene la evidencia anti-lesión del warmup pero sí evidencia para recovery
 * subjetiva y adherencia.
 *
 * Algoritmo (espejo de WarmupMissingValidator):
 *   1. $.vuelta_calma / $.cooldown / $.enfriamiento top-level (string no vacío)
 *   2. $.semanas[*].dias[*].vuelta_calma / .cooldown / .enfriamiento
 *   3. Mención en $.tips[*] o $.notas_coach con keywords
 *   4. Si nada → warning con paths examinados
 *
 * Severidad: warning (no error) — un plan sin cooldown sirve, solo es subóptimo.
 *
 * Usado por: heur_cooldown_missing.
 */
final class CooldownMissingValidator extends BaseValidator
{
    private const KEYWORDS = [
        'vuelta a la calma',
        'vuelta calma',
        'cooldown',
        'cool-down',
        'cool down',
        'enfriamiento',
        'enfriar',
        // Variantes coloquiales
        'estiramiento final',
        'estiramientos final',
        'estiramientos al final',
        'estiramiento post',
        'estiramientos post',
        'bajar pulsaciones',
    ];

    private const STRUCTURED_KEYS = ['vuelta_calma', 'cooldown', 'enfriamiento'];

    public function name(): string
    {
        return 'cooldown_missing';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;

        if (($plan['plan_type'] ?? null) !== 'entrenamiento') {
            return [];
        }

        // 1. Top-level
        foreach (self::STRUCTURED_KEYS as $k) {
            if (! empty($plan[$k])) {
                return [];
            }
        }

        // 2. Por día
        foreach (($plan['semanas'] ?? []) as $sem) {
            foreach (($sem['dias'] ?? []) as $dia) {
                foreach (self::STRUCTURED_KEYS as $k) {
                    if (! empty($dia[$k])) {
                        return [];
                    }
                }
            }
        }

        // 3. Tips
        foreach ((array) ($plan['tips'] ?? []) as $tip) {
            if (is_string($tip) && $this->containsKeyword($tip)) {
                return [];
            }
        }

        // 4. Notas coach
        $notas = (string) ($plan['notas_coach'] ?? '');
        if ($notas !== '' && $this->containsKeyword($notas)) {
            return [];
        }

        return [$this->makeViolation(
            $ctx,
            '$',
            'Plan de entrenamiento sin mención de vuelta a la calma / cooldown en ningún path. Mejora recovery subjetiva y adherencia. Agregar 5 min estiramiento final o caminata suave en tips o notas_coach.',
            [
                'searched_keys' => self::STRUCTURED_KEYS,
                'searched_keywords' => self::KEYWORDS,
            ],
        )];
    }

    private function containsKeyword(string $text): bool
    {
        $lower = mb_strtolower($text);
        foreach (self::KEYWORDS as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }
        return false;
    }
}
