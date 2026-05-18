<?php

declare(strict_types=1);

namespace App\Services\DecisionEngine\Data;

/**
 * Resultado agregado de correr el DecisionEngine contra un ClientProfile.
 *
 * Las recommendations vienen agrupadas por vertical para que el caller pueda
 * separar "qué metodología de entrenamiento usar" vs "qué de nutrición" vs
 * "qué stack de suplementos", etc.
 *
 * Dentro de cada vertical, están ordenadas por confidence descendente.
 */
final readonly class DecisionResult
{
    /**
     * @param array<string, Recommendation[]> $byVertical Map vertical → ordered recommendations
     * @param Recommendation[] $all Lista flat de todas las recomendaciones (mismo orden)
     */
    public function __construct(
        public array $byVertical,
        public array $all,
        public int $rulesEvaluated,
        public int $rulesMatched,
        public float $durationMs,
    ) {
    }

    /**
     * Retorna la mejor recomendación por vertical (top confidence).
     *
     * @return array<string, Recommendation>
     */
    public function topPerVertical(): array
    {
        $top = [];
        foreach ($this->byVertical as $vertical => $recs) {
            if ($recs !== []) {
                $top[$vertical] = $recs[0];
            }
        }
        return $top;
    }

    /**
     * @return Recommendation[]
     */
    public function forVertical(string $vertical): array
    {
        return $this->byVertical[$vertical] ?? [];
    }

    public function summary(): array
    {
        return [
            'rules_evaluated' => $this->rulesEvaluated,
            'rules_matched' => $this->rulesMatched,
            'verticals_covered' => array_keys(array_filter($this->byVertical, fn ($r) => $r !== [])),
            'duration_ms' => round($this->durationMs, 2),
        ];
    }
}
