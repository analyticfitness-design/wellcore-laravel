<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta planes de suplementación SIN vitamina D3.
 *
 * Razón: deficiencia de vitamina D es prevalente en LATAM urbano (oficinistas
 * con exposición solar mínima). Niveles bajos correlacionan con baja testosterona,
 * recovery pobre, ánimo bajo, lesiones articulares.
 *
 * Dosis estándar: 2000-5000 IU/día con grasa. Mejor con K2 (MK-7) para
 * direccionar calcio al hueso (no a arterias).
 *
 * Detección por substring "vitamina d" / "vit d" / "d3" en slug o nombre.
 *
 * Usado por: heur_supl_vitamina_d3_missing.
 */
final class VitaminaD3MissingValidator extends BaseValidator
{
    public function name(): string
    {
        return 'vitamina_d3_missing';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;

        if (($plan['plan_type'] ?? null) !== 'suplementacion') {
            return [];
        }

        $suplementos = $plan['suplementos'] ?? [];
        if (! is_array($suplementos)) {
            return [];
        }

        foreach ($suplementos as $sup) {
            $slug = mb_strtolower((string) ($sup['slug'] ?? ''));
            $nombre = mb_strtolower((string) ($sup['nombre'] ?? ''));
            $haystack = $slug . ' ' . $nombre;
            if (str_contains($haystack, 'vitamina d')
                || str_contains($haystack, 'vit d')
                || str_contains($haystack, 'vit_d')
                || str_contains($haystack, 'd3 ')
                || str_contains($haystack, 'd-3')
                || str_ends_with($slug, 'd3')
                || str_ends_with($nombre, 'd3')) {
                return [];
            }
        }

        return [$this->makeViolation(
            $ctx,
            '$.suplementos',
            'Plan de suplementación SIN vitamina D3. Deficiencia muy prevalente en LATAM urbano. 2000-5000 IU/día con grasa. Considerar D3 + K2 (MK-7) para direccionar calcio óseo.',
            [
                'suplementos_count' => count($suplementos),
                'recomendacion' => 'Slug "vitamina_d3" — 2000-5000 IU/día con comida grasa (desayuno o almuerzo). Ideal con K2 (100-200 mcg MK-7).',
            ],
        )];
    }
}
