<?php

declare(strict_types=1);

namespace App\Services\DecisionEngine;

use App\Models\Kb\DecisionRule;
use App\Services\DecisionEngine\Data\ClientProfile;
use App\Services\DecisionEngine\Data\DecisionResult;
use App\Services\DecisionEngine\Data\Recommendation;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Stage 2 SELECT del motor v2: dado un ClientProfile, retorna las metodologías
 * recomendadas para cada vertical (entrenamiento, nutrición, suplementación,
 * hábitos, ciclo).
 *
 * Algoritmo:
 *   1. Carga decision_rules activas con su methodology eager-loaded.
 *   2. Para cada rule, evalúa WhenMatcher contra el profile.
 *   3. Si matchea, construye Recommendation con confidence + rationale.
 *   4. Agrupa por vertical (heredado del slug/vertical del methodology).
 *   5. Ordena cada grupo por confidence DESC.
 *   6. Retorna DecisionResult.
 *
 * Multi-rule matching: pueden matchear N rules para el mismo vertical. El caller
 * decide si usar solo top o todas (ej. fallback chain).
 */
final class DecisionEngine
{
    public function __construct(
        private readonly WhenMatcher $matcher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function decide(ClientProfile $profile): DecisionResult
    {
        $start = microtime(true);
        $rules = $this->loadRules();
        $byVertical = [];
        $all = [];
        $matched = 0;

        foreach ($rules as $rule) {
            try {
                $whenJson = is_array($rule->when_json) ? $rule->when_json : [];
                $result = $this->matcher->evaluate($whenJson, $profile);
                if (! $result['matched']) {
                    continue;
                }
                $matched++;

                $methodology = $rule->methodology;
                if ($methodology === null) {
                    $this->logger->warning("DecisionEngine: rule #$rule->id matchea pero su methodology está eliminada.");
                    continue;
                }

                $vertical = (string) ($methodology->vertical ?? $whenJson['vertical'] ?? 'unknown');
                $recommendation = new Recommendation(
                    ruleId: (int) $rule->id,
                    ruleName: (string) $rule->name,
                    methodologyId: (int) $methodology->id,
                    methodologySlug: (string) $methodology->slug,
                    methodologyName: (string) $methodology->name,
                    vertical: $vertical,
                    confidence: (float) $rule->confidence,
                    rationale: (string) $rule->rationale,
                    matchedConditions: $result['matched_conditions'],
                );
                $byVertical[$vertical][] = $recommendation;
                $all[] = $recommendation;
            } catch (Throwable $e) {
                $this->logger->error(
                    "DecisionEngine: rule #$rule->id falló: " . $e->getMessage(),
                    ['exception' => $e],
                );
            }
        }

        // Ordenar cada vertical por confidence DESC (rule_id ASC para tiebreak determinista)
        foreach ($byVertical as $v => $recs) {
            usort($recs, function (Recommendation $a, Recommendation $b) {
                $diff = $b->confidence <=> $a->confidence;
                return $diff !== 0 ? $diff : $a->ruleId <=> $b->ruleId;
            });
            $byVertical[$v] = $recs;
        }

        // Lista flat ordenada igual
        usort($all, function (Recommendation $a, Recommendation $b) {
            $diff = $b->confidence <=> $a->confidence;
            return $diff !== 0 ? $diff : $a->ruleId <=> $b->ruleId;
        });

        $duration = (microtime(true) - $start) * 1000;
        return new DecisionResult(
            byVertical: $byVertical,
            all: $all,
            rulesEvaluated: count($rules),
            rulesMatched: $matched,
            durationMs: $duration,
        );
    }

    /**
     * @return Collection<int, DecisionRule>
     */
    private function loadRules(): Collection
    {
        return DecisionRule::query()
            ->active()
            ->with('methodology')
            ->orderByDesc('confidence')
            ->get();
    }
}
