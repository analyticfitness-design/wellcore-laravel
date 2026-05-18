<?php

declare(strict_types=1);

namespace App\Services\DecisionEngine;

use App\Services\DecisionEngine\Data\ClientProfile;

/**
 * Evalúa el `when_json` de una decision_rule contra un ClientProfile.
 *
 * Reglas de match (legacy schema):
 *  - TODAS las keys de when_json deben estar presentes y ser iguales en el profile (AND)
 *  - Keys extra en el profile no afectan el match
 *  - Si una key del when no existe en el profile → no match
 *  - Comparación strict (===) para evitar coerciones (1 vs "1" no matchea)
 *
 * Soporta valores escalares simples y arrays. Para arrays interpreta:
 *   - when.injuries = ["lumbalgia"] → profile.injuries debe CONTENER "lumbalgia"
 */
final class WhenMatcher
{
    /**
     * @return array{matched: bool, matched_conditions: array<string, mixed>}
     */
    public function evaluate(array $whenJson, ClientProfile $profile): array
    {
        $profileData = $profile->toArray();
        $matchedConditions = [];

        foreach ($whenJson as $key => $expected) {
            if (! array_key_exists($key, $profileData)) {
                return ['matched' => false, 'matched_conditions' => []];
            }
            $actual = $profileData[$key];

            if (! $this->valuesMatch($expected, $actual)) {
                return ['matched' => false, 'matched_conditions' => []];
            }
            $matchedConditions[$key] = $actual;
        }

        return ['matched' => true, 'matched_conditions' => $matchedConditions];
    }

    private function valuesMatch(mixed $expected, mixed $actual): bool
    {
        // when.injuries = [...] significa "actual debe contener al menos uno"
        if (is_array($expected) && is_array($actual)) {
            return array_intersect($expected, $actual) !== [];
        }

        // when.foo = [valor1, valor2] sin profile array → IN (cualquiera)
        if (is_array($expected) && ! is_array($actual)) {
            return in_array($actual, $expected, true);
        }

        // Escalares: comparación strict para evitar coerciones
        // Excepción: int/string numérico — comparar como int
        if (is_numeric($expected) && is_numeric($actual)) {
            return (float) $expected === (float) $actual;
        }

        return $expected === $actual;
    }
}
