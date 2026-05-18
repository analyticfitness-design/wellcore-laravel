<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Data;

/**
 * Output del ComposeEngine. El `planJson` está listo para pasar al LintEngine
 * y luego (en Sprint 5+) al PersistEngine.
 */
final readonly class ComposeResult
{
    public function __construct(
        /** @var array<string,mixed> JSON shape igual al sample-good-plan.json */
        public array $planJson,
        /** @var string[] warnings no-fatales (ej. "no se encontraron isolaciones para gemelo") */
        public array $warnings,
        public float $durationMs,
    ) {
    }
}
