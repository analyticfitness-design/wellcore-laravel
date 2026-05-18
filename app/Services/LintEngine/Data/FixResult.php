<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Data;

/**
 * Resultado agregado de pasar AutoFix engine sobre un plan.
 *
 * Lleva:
 *  - $fixedPlan: el plan final tras aplicar todos los fixes
 *  - $appliedFixes: lista de fixes aplicados
 *  - $skipped: count de violations que no eran auto-fixable
 *  - $failed: count de fixes que fallaron al aplicarse
 *  - $remainingViolations: violations que persisten tras el fix pass
 */
final readonly class FixResult
{
    /**
     * @param AppliedFix[] $appliedFixes
     * @param Violation[] $remainingViolations
     */
    public function __construct(
        public array $fixedPlan,
        public array $appliedFixes,
        public int $skipped,
        public int $failed,
        public array $remainingViolations,
        public float $durationMs,
    ) {
    }

    public function applied(): int
    {
        return count($this->appliedFixes);
    }

    public function summary(): array
    {
        return [
            'applied' => $this->applied(),
            'skipped_not_auto_fixable' => $this->skipped,
            'failed' => $this->failed,
            'remaining_violations' => count($this->remainingViolations),
            'remaining_errors' => count(array_filter($this->remainingViolations, fn (Violation $v) => $v->severity === 'error')),
            'duration_ms' => round($this->durationMs, 2),
        ];
    }
}
