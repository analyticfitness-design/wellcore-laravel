<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta planes de suplementación SIN creatina como suplemento.
 *
 * Razón: creatina monohidrato es el suplemento más estudiado y efectivo.
 * Salvo contraindicación renal documentada, todo plan de suplementación con
 * objetivo de ganancia de fuerza/masa debería incluirla. Su ausencia es señal
 * de que el coach olvidó el suplemento "default" más eficaz costo-beneficio.
 *
 * Excepciones (no aplica):
 *   - Cliente tiene preference 'contraindicacion_renal' → skip
 *   - Plan tiene 'objetivo' que claramente NO requiere creatina
 *     (ej. dieta keto pura: la creatina no es relevante)
 *
 * Algoritmo:
 *   1. Verifica que plan_type sea suplementacion
 *   2. Busca en suplementos[].slug entries que matcheen "creatina"
 *   3. Si ninguno, warning
 *
 * Usado por: heur_supl_creatina_missing.
 */
final class CreatinaMissingValidator extends BaseValidator
{
    public function name(): string
    {
        return 'creatina_missing';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;

        // Solo aplica a plan_type=suplementacion
        if (($plan['plan_type'] ?? null) !== 'suplementacion') {
            return [];
        }

        $suplementos = $plan['suplementos'] ?? [];
        if (! is_array($suplementos)) {
            return [];
        }

        // Buscar creatina por slug o nombre (case-insensitive)
        foreach ($suplementos as $sup) {
            $slug = mb_strtolower((string) ($sup['slug'] ?? ''));
            $nombre = mb_strtolower((string) ($sup['nombre'] ?? ''));
            if (str_contains($slug, 'creatina') || str_contains($nombre, 'creatina')) {
                return [];
            }
        }

        // No se encontró creatina → violation
        return [$this->makeViolation(
            $ctx,
            '$.suplementos',
            'Plan de suplementación SIN creatina. Es el suplemento con mejor evidencia (Schoenfeld 2018, ISSN position statement). 5g/día monohidrato. Solo skipear si contraindicación renal documentada.',
            [
                'suplementos_count' => count($suplementos),
                'recomendacion' => 'Agregar creatina monohidrato 5g/día — barato, efectivo, evidence_level very_high',
            ],
        )];
    }
}
