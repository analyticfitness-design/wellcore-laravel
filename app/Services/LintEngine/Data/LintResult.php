<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Data;

/**
 * Resultado agregado de correr el linter contra un plan.
 *
 * Inmutable. Permite al caller decidir si el plan pasa a PERSIST (Stage 5)
 * o se rechaza: passes() === false si hay al menos 1 violation con severity=error.
 */
final readonly class LintResult
{
    /** @param Violation[] $violations */
    public function __construct(
        public array $violations,
        public int $rulesEvaluated,
        public int $rulesSkipped,
        public float $durationMs,
    ) {
    }

    /**
     * @return Violation[]
     */
    public function errors(): array
    {
        return array_values(array_filter($this->violations, fn (Violation $v) => $v->severity === 'error'));
    }

    /**
     * @return Violation[]
     */
    public function warnings(): array
    {
        return array_values(array_filter($this->violations, fn (Violation $v) => $v->severity === 'warning'));
    }

    /**
     * @return Violation[]
     */
    public function infos(): array
    {
        return array_values(array_filter($this->violations, fn (Violation $v) => $v->severity === 'info'));
    }

    /**
     * El plan pasa si no hay errors. Warnings y infos no bloquean PERSIST.
     */
    public function passes(): bool
    {
        return count($this->errors()) === 0;
    }

    /**
     * Exit code para CLI: 0 clean, 1 con warnings, 2 con errors.
     */
    public function exitCode(): int
    {
        if (count($this->errors()) > 0) {
            return 2;
        }
        if (count($this->warnings()) > 0) {
            return 1;
        }
        return 0;
    }

    public function summary(): array
    {
        return [
            'rules_evaluated' => $this->rulesEvaluated,
            'rules_skipped' => $this->rulesSkipped,
            'errors' => count($this->errors()),
            'warnings' => count($this->warnings()),
            'infos' => count($this->infos()),
            'duration_ms' => round($this->durationMs, 2),
            'passes' => $this->passes(),
        ];
    }
}
