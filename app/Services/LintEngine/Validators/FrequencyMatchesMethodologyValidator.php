<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Models\Kb\Methodology;
use App\Services\LintEngine\Data\LintContext;
use Throwable;

/**
 * Verifica que `frecuencia_dias` del plan coincida con `target_days_min/max`
 * de la metodología nombrada en el plan.
 *
 * Algoritmo:
 *   1. Lee `plan.metodologia` (string libre, ej. "PPL (Push/Pull/Legs) 6 días")
 *   2. Lee `plan.frecuencia_dias` (int, ej. 6)
 *   3. Busca en wellcore_kb.methodologies la entry cuyo `name` matchee el string del plan.
 *   4. Si la encontró y `target_days_min/max` están seteados, valida que
 *      frecuencia_dias ∈ [min, max].
 *
 * Si no encuentra methodology o no tiene target_days, skip (no genera violation).
 *
 * Usado por: heur_frequency_methodology_mismatch.
 */
final class FrequencyMatchesMethodologyValidator extends BaseValidator
{
    public function name(): string
    {
        return 'frequency_matches_methodology';
    }

    public function check(LintContext $ctx): array
    {
        $metodologiaName = $ctx->plan['metodologia'] ?? null;
        $frecuenciaDias = $ctx->plan['frecuencia_dias'] ?? null;

        if (! is_string($metodologiaName) || ! is_int($frecuenciaDias)) {
            return [];
        }

        $methodology = $this->findMethodology($metodologiaName);
        if ($methodology === null) {
            return [];
        }

        $min = $methodology->target_days_min;
        $max = $methodology->target_days_max;

        // Si no hay target_days definidos, no validamos.
        if ($min === null && $max === null) {
            return [];
        }

        $minEff = $min ?? 1;
        $maxEff = $max ?? 7;

        if ($frecuenciaDias < $minEff || $frecuenciaDias > $maxEff) {
            return [$this->makeViolation(
                $ctx,
                '$.frecuencia_dias',
                sprintf(
                    "Mismatch frecuencia/methodology: el plan declara %d días/semana pero la metodología '%s' espera %d-%d días. Revisar split o cambiar de methodology.",
                    $frecuenciaDias,
                    $methodology->name,
                    $minEff, $maxEff,
                ),
                [
                    'frecuencia_plan' => $frecuenciaDias,
                    'methodology_slug' => $methodology->slug,
                    'target_min' => $minEff,
                    'target_max' => $maxEff,
                ],
            )];
        }

        return [];
    }

    /**
     * Encuentra la methodology que matchea el string libre del plan.
     * Match flexible: exact name → contains key tokens.
     */
    private function findMethodology(string $rawName): ?Methodology
    {
        try {
            // Exact name match
            $found = Methodology::query()->where('name', $rawName)->first();
            if ($found !== null) {
                return $found;
            }

            // Substring match — el plan dice "PPL 6 días" pero el name es "PPL (Push/Pull/Legs) 6 días"
            // Buscamos por tokens distintivos.
            $tokens = $this->extractTokens($rawName);
            if ($tokens === []) {
                return null;
            }

            $candidates = Methodology::query()->where('vertical', 'entrenamiento')->get();
            foreach ($candidates as $m) {
                $mTokens = $this->extractTokens((string) $m->name);
                if (count(array_intersect($tokens, $mTokens)) >= 2) {
                    return $m;
                }
            }
        } catch (Throwable) {
            return null;
        }
        return null;
    }

    /**
     * Extrae tokens significativos de un nombre de methodology.
     * @return string[]
     */
    private function extractTokens(string $name): array
    {
        $clean = mb_strtolower($name);
        $clean = preg_replace('/[()\\/]/u', ' ', $clean) ?? '';
        $clean = preg_replace('/\s+/', ' ', $clean) ?? '';
        $tokens = array_filter(explode(' ', $clean), fn ($t) => mb_strlen($t) >= 3 && ! in_array($t, ['con', 'por', 'para', 'del', 'los', 'las'], true));
        return array_values($tokens);
    }
}
