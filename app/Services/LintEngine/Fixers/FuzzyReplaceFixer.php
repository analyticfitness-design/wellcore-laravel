<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Fixers;

use App\Services\LintEngine\Data\AppliedFix;
use App\Services\LintEngine\Data\FixContext;

/**
 * Reemplaza el valor por el `allowed_value` más cercano (Levenshtein).
 *
 * Usado por: schema_train_invalid_phase_name
 *   "adaptacion" → "Adaptación"
 *   "fuerza maxima" → "Fuerza Máxima"
 *
 * Parámetros del auto_fix:
 *   - max_distance: distancia Levenshtein máxima aceptable (default 2)
 *   - min_confidence: si distance/max(len) > 1 - min_confidence, NO aplica
 *
 * Lee allowed_values desde la rule.check_definition (no del auto_fix).
 */
final class FuzzyReplaceFixer extends BaseFixer
{
    public function name(): string
    {
        return 'fuzzy_replace';
    }

    public function apply(FixContext $ctx): ?AppliedFix
    {
        $maxDistance = (int) ($ctx->autoFixDefinition['max_distance'] ?? 2);
        $minConfidence = (float) ($ctx->autoFixDefinition['min_confidence'] ?? 0.85);

        // allowed_values vive en check_definition top-level (no en auto_fix)
        // El context no lo trae directo — reconstruimos desde rule meta es overkill.
        // Por simplicidad: el caller debe haberlo puesto en auto_fix.allowed_values
        // O lo extraemos del violation.foundValue context (no disponible).
        // Solución: el seed pone allowed_values dentro de auto_fix para fixer.
        $allowed = $ctx->autoFixDefinition['allowed_values']
            ?? $ctx->autoFixDefinition['candidates']
            ?? null;

        if (! is_array($allowed) || $allowed === []) {
            // Fallback: hardcode las 9 fases oficiales (caso #1 de uso de este fixer)
            $allowed = ['Adaptación', 'Hipertrofia', 'Fuerza', 'Fuerza Máxima', 'Peak', 'Deload', 'Recuperación', 'Preparación', 'Mantenimiento'];
        }

        $path = $ctx->violation->jsonPath;
        $current = $this->mutator->getAtPath($ctx->plan, $path);
        if (! is_string($current)) {
            return null;
        }

        // Si el valor YA empieza con algún allowed exactamente (case-sensitive, con tildes), skip
        // OJO: NO lowercased — "hipertrofia" sin tilde NO debe considerarse igual a "Hipertrofia"
        foreach ($allowed as $candidate) {
            if (str_starts_with($current, (string) $candidate)) {
                return null;
            }
        }

        // Buscar mejor match por Levenshtein
        $best = null;
        $bestDistance = PHP_INT_MAX;
        $currentLower = mb_strtolower($current);

        // Si el valor tiene sufijo tipo "adaptacion · RIR 3", solo comparamos prefijo
        $prefix = explode(' · ', $current, 2)[0];
        $prefixLower = mb_strtolower($prefix);

        foreach ($allowed as $candidate) {
            $distance = levenshtein($prefixLower, mb_strtolower($candidate));
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $best = $candidate;
            }
        }

        if ($best === null) {
            return null;
        }

        // Validar confianza
        if ($bestDistance > $maxDistance) {
            return null;
        }
        $maxLen = max(mb_strlen($prefix), mb_strlen($best));
        $similarity = $maxLen === 0 ? 0 : 1 - ($bestDistance / $maxLen);
        if ($similarity < $minConfidence) {
            return null;
        }

        // Reconstruir el valor preservando el sufijo si lo había
        $suffix = '';
        if (str_contains($current, ' · ')) {
            $parts = explode(' · ', $current, 2);
            $suffix = ' · ' . ($parts[1] ?? '');
        }
        $newValue = $best . $suffix;

        $fixedPlan = $this->mutator->setAtPath($ctx->plan, $path, $newValue);
        return $this->makeApplied(
            $ctx,
            before: $current,
            after: $newValue,
            summary: "Fuzzy replace: \"$current\" → \"$newValue\" (distance=$bestDistance, similarity=" . round($similarity * 100) . "%)",
            fixedPlan: $fixedPlan,
        );
    }
}
