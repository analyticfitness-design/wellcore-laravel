<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta planes donde todas las semanas son idénticas — periodización ausente.
 *
 * Compara las semanas excluyendo metadata variable (numero, fase, fase_nombre,
 * descripcion, nota_semana). Lo que importa es: ¿la PROGRAMACIÓN cambia?
 *
 * Usado por: heur_missing_progression.
 */
final class WeeksAreIdenticalValidator extends BaseValidator
{
    public function name(): string
    {
        return 'weeks_are_identical';
    }

    public function check(LintContext $ctx): array
    {
        $minWeeks = (int) ($ctx->checkDefinition['min_weeks'] ?? 4);
        $semanas = $ctx->plan['semanas'] ?? [];

        if (! is_array($semanas) || count($semanas) < $minWeeks) {
            return [];
        }

        // Normalizar cada semana excluyendo campos variables esperables
        $skipKeys = ['numero', 'semana', 'fase', 'fase_nombre', 'nombre_bloque', 'descripcion', 'nota_semana', 'rpe_objetivo', 'titulo', 'es_actual', 'completada'];

        $normalized = [];
        foreach ($semanas as $s) {
            if (! is_array($s)) continue;
            $filtered = array_diff_key($s, array_flip($skipKeys));
            $normalized[] = json_encode($filtered, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $unique = array_unique($normalized);
        if (count($unique) === 1 && count($normalized) >= $minWeeks) {
            return [$this->makeViolation(
                $ctx,
                '$.semanas',
                sprintf(
                    "Las %d semanas son idénticas en su programación (mismos ejercicios, series, reps, RIR). El plan no tiene progresión efectiva.",
                    count($semanas),
                ),
                ['total_weeks' => count($semanas), 'unique_weeks' => 1],
            )];
        }

        return [];
    }
}
