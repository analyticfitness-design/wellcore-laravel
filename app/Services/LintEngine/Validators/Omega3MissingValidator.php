<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta planes de suplementación SIN omega-3 (EPA+DHA).
 *
 * Razón: omega-3 EPA+DHA tiene evidencia consistente para:
 * - Reducción de inflamación post-entreno (DOMS)
 * - Recuperación cardiovascular
 * - Salud articular crónica
 * Dosis estándar: 1-2g EPA+DHA combinados/día.
 *
 * Detección por substring "omega" en slug o nombre.
 *
 * Usado por: heur_supl_omega3_missing.
 */
final class Omega3MissingValidator extends BaseValidator
{
    public function name(): string
    {
        return 'omega3_missing';
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
            if (str_contains($slug, 'omega') || str_contains($nombre, 'omega')) {
                return [];
            }
            // También aceptar "aceite de pescado" / "fish oil" como nombres alternativos
            if (str_contains($nombre, 'aceite de pescado') || str_contains($nombre, 'fish oil')) {
                return [];
            }
        }

        return [$this->makeViolation(
            $ctx,
            '$.suplementos',
            'Plan de suplementación SIN omega-3 (EPA+DHA). Evidencia consistente para reducción de inflamación, recuperación cardiovascular y salud articular. Dosis recomendada: 1-2g EPA+DHA/día.',
            [
                'suplementos_count' => count($suplementos),
                'recomendacion' => 'Agregar omega-3 con 1-2g EPA+DHA combinados/día. Origen: pescado azul (sardina/salmón) o suplemento purificado.',
            ],
        )];
    }
}
