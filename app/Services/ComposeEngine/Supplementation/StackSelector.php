<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Supplementation;

use App\Models\Kb\SupplementStack;
use App\Services\DecisionEngine\Data\ClientProfile;

/**
 * Escoge un supplement_stack para un ClientProfile.
 *
 * Algoritmo:
 *   1. Filtra stacks activos.
 *   2. Filtra por tier (stack.applicable_tier_min ≤ profile.tier).
 *   3. Prefiere stacks cuyo applicable_objectives contenga profile.goal.
 *   4. Prefiere stacks cuyo applicable_genders contenga profile.gender (M/F).
 *   5. Tiebreak: el menos costo (approximate_monthly_cost_cop ASC).
 *
 * Si no hay match exacto, fallback al stack `stack-basico-esencial-universal`
 * que cubre cualquier perfil de forma segura.
 */
final class StackSelector
{
    private const TIER_RANK = [
        'trial' => 1,
        'esencial' => 2,
        'metodo' => 3,
        'elite' => 4,
        'rise' => 5,
    ];

    private const FALLBACK_SLUG = 'stack-basico-esencial-universal';

    public function selectFor(ClientProfile $profile): ?SupplementStack
    {
        $clientTierRank = self::TIER_RANK[$profile->tier ?? 'esencial'] ?? 2;
        $allowedTiers = array_keys(array_filter(self::TIER_RANK, fn ($r) => $r <= $clientTierRank));

        $candidates = SupplementStack::query()
            ->active()
            ->whereIn('applicable_tier_min', $allowedTiers)
            ->orderBy('approximate_monthly_cost_cop')
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        $scored = $candidates->map(function (SupplementStack $s) use ($profile) {
            $score = 0;

            $objectives = $s->applicable_objectives ?? [];
            if ($profile->goal !== null && in_array($profile->goal, $objectives, true)) {
                $score += 10;
            }

            $genders = $s->applicable_genders ?? [];
            $genderTokens = $this->genderTokens($profile->gender);
            $genderSpecific = array_values(array_filter($genders, fn ($g) => ! in_array($g, ['any', 'ambos'], true)));
            $isStackGenderSpecific = $genderSpecific !== [] && count($genderSpecific) <= 1;
            $genderMatches = $genderTokens !== [] && array_intersect($genderTokens, $genders) !== [];

            if ($genderMatches) {
                // Bonus extra alto si el stack es gender-SPECIFIC y matchea.
                $score += $isStackGenderSpecific ? 8 : 5;
            } elseif ($isStackGenderSpecific && $genderTokens !== []) {
                // El stack es gender-specific pero NO matchea con el profile → descartar.
                $score -= 100;
            }

            // Stacks "any"/"ambos" o sin gender específico tienen score base.
            if ($genders === [] || in_array('any', $genders, true) || in_array('ambos', $genders, true)) {
                $score += 2;
            }

            $levels = $s->applicable_levels ?? [];
            if ($profile->level !== null && in_array($profile->level, $levels, true)) {
                $score += 3;
            }
            if ($levels === [] || in_array('any', $levels, true)) {
                $score += 1;
            }

            return ['stack' => $s, 'score' => $score];
        })->sortByDesc('score');

        // El de mayor score; si todos score≤0, fallback al universal.
        $top = $scored->first();
        if ($top['score'] <= 0) {
            $fallback = $candidates->firstWhere('slug', self::FALLBACK_SLUG);
            return $fallback ?? $candidates->first();
        }

        return $top['stack'];
    }

    /**
     * Tokens equivalentes para gender — matchea cualquier formato usado en seeds
     * (los catalogs usan "femenino"/"masculino", el profile puede usar "F"/"M").
     *
     * @return string[]
     */
    private function genderTokens(?string $g): array
    {
        if ($g === null) {
            return [];
        }
        $lower = strtolower($g);

        if (in_array($lower, ['f', 'femenino', 'female', 'mujer'], true)) {
            return ['F', 'f', 'femenino', 'female', 'mujer'];
        }
        if (in_array($lower, ['m', 'masculino', 'male', 'hombre'], true)) {
            return ['M', 'm', 'masculino', 'male', 'hombre'];
        }
        return [$g];
    }
}
