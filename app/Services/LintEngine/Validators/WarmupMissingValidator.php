<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta planes de entrenamiento sin mención explícita de calentamiento.
 *
 * Razón: el calentamiento previene 30-50% de lesiones agudas (gym).
 * Un plan sin warmup en ninguna parte tiene riesgo elevado para principiantes
 * e intermedios que no lo saben de memoria.
 *
 * Algoritmo:
 *   1. Busca en estos paths la mención de "calentamiento" / "warmup" / "calienta":
 *      - $.calentamiento (top-level)
 *      - $.semanas[*].dias[*].calentamiento (per-day)
 *      - $.tips[*] (cualquier tip que mencione)
 *      - $.notas_coach (mención dentro de notas)
 *   2. Si ninguno menciona, genera warning.
 *
 * Usado por: heur_warmup_missing.
 */
final class WarmupMissingValidator extends BaseValidator
{
    private const KEYWORDS = [
        // Cubre: calentamiento, calentá, calentado (raíz calent-)
        'calent',
        // Cubre: calienta, caliente (raíz calient- con i entre l-e)
        'calient',
        'warmup',
        'warm-up',
        'warm up',
        'preparacion',
        'preparación',
        'movilidad articular',
    ];

    public function name(): string
    {
        return 'warmup_missing';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;

        // 1. Top-level calentamiento (string no vacío)
        if (! empty($plan['calentamiento'])) {
            return [];
        }

        // 2. Por día: si AL MENOS un día tiene calentamiento, OK
        $semanas = $plan['semanas'] ?? [];
        if (is_array($semanas)) {
            foreach ($semanas as $sem) {
                foreach (($sem['dias'] ?? []) as $dia) {
                    if (! empty($dia['calentamiento'])) {
                        return [];
                    }
                }
            }
        }

        // 3. Tips
        $tips = $plan['tips'] ?? [];
        foreach ((array) $tips as $tip) {
            if (is_string($tip) && $this->containsKeyword($tip)) {
                return [];
            }
        }

        // 4. Notas coach
        $notas = (string) ($plan['notas_coach'] ?? '');
        if ($notas !== '' && $this->containsKeyword($notas)) {
            return [];
        }

        // Si llegamos acá: ningún path menciona calentamiento → violation
        return [$this->makeViolation(
            $ctx,
            '$',
            'Plan de entrenamiento sin mención de calentamiento en ningún path (top-level, días, tips ni notas_coach). Riesgo de lesión aguda elevado. Agregar al menos un tip o nota recomendando warmup específico de 5-10 min.',
            [
                'searched_paths' => ['$.calentamiento', '$.semanas[*].dias[*].calentamiento', '$.tips[*]', '$.notas_coach'],
                'keywords' => self::KEYWORDS,
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
