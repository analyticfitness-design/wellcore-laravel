<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Verifica que cuando el plan menciona calentamiento, indique una duración mínima razonable.
 *
 * Razón: un "warmup de 1-2 min" no cumple su función fisiológica (elevar temperatura
 * tisular, lubricar articulaciones, activar SN). Mínimo razonable: 5 min general,
 * 8-10 min para sesiones pesadas/cliente con lesión.
 *
 * Solo aplica a plan_type=entrenamiento.
 *
 * Algoritmo:
 *   1. Busca texto de calentamiento (top-level, días, tips, notas).
 *   2. Si encuentra texto, parsea pattern "(\d+)\s*(min|minutos)" para duración.
 *   3. Si encuentra duración Y es <5 → warning.
 *   4. Si no encuentra duración explícita pero hay mención → info (no warning).
 *
 * Usado por: heur_warmup_min_duration.
 */
final class WarmupMinDurationValidator extends BaseValidator
{
    private const MIN_MIN = 5;
    private const KEYWORDS = ['calent', 'calient', 'warmup', 'warm-up', 'warm up'];

    public function name(): string
    {
        return 'warmup_min_duration';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;

        if (($plan['plan_type'] ?? null) !== 'entrenamiento') {
            return [];
        }

        $textos = $this->collectWarmupTexts($plan);
        if ($textos === []) {
            return []; // WarmupMissingValidator cubre el caso "no mention"
        }

        $minMin = (int) ($ctx->checkDefinition['min_min'] ?? self::MIN_MIN);

        $tooShort = [];
        foreach ($textos as $texto) {
            if (preg_match('/(\d+)\s*(?:min|minutos)\b/u', mb_strtolower($texto), $m)) {
                $minutos = (int) $m[1];
                if ($minutos > 0 && $minutos < $minMin) {
                    $tooShort[] = ['texto_snippet' => mb_substr($texto, 0, 80), 'minutos' => $minutos];
                }
            }
        }

        if ($tooShort === []) {
            return [];
        }

        return [$this->makeViolation(
            $ctx,
            '$.calentamiento',
            sprintf(
                'Calentamiento con duración <%d min detectada. Mínimo fisiológico: %d min (8-10 para sesiones pesadas o con lesiones).',
                $minMin, $minMin,
            ),
            [
                'min_min_required' => $minMin,
                'matches' => $tooShort,
            ],
        )];
    }

    /**
     * @return string[]
     */
    private function collectWarmupTexts(array $plan): array
    {
        $out = [];
        if (! empty($plan['calentamiento'])) {
            $out[] = (string) $plan['calentamiento'];
        }
        foreach (($plan['semanas'] ?? []) as $sem) {
            foreach (($sem['dias'] ?? []) as $dia) {
                if (! empty($dia['calentamiento'])) {
                    $out[] = (string) $dia['calentamiento'];
                }
            }
        }
        foreach ((array) ($plan['tips'] ?? []) as $tip) {
            if (is_string($tip) && $this->mentionsWarmup($tip)) {
                $out[] = $tip;
            }
        }
        $notas = (string) ($plan['notas_coach'] ?? '');
        if ($notas !== '' && $this->mentionsWarmup($notas)) {
            $out[] = $notas;
        }
        return $out;
    }

    private function mentionsWarmup(string $text): bool
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
