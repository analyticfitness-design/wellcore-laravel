<?php

declare(strict_types=1);

namespace App\Services\PersistEngine\Data;

use App\Services\ComposeEngine\Data\ComposeResult;
use App\Services\DecisionEngine\Data\ClientProfile;
use App\Services\LintEngine\Data\AppliedFix;
use App\Services\LintEngine\Data\LintResult;

/**
 * Input inmutable del PersistService.
 *
 * Agrupa todo lo necesario para grabar un audit row de un plan: profile,
 * compose output, lint pre-fix, fixes aplicados, lint post-fix.
 */
final readonly class PersistInput
{
    /**
     * @param AppliedFix[] $fixesApplied
     */
    public function __construct(
        public ClientProfile $profile,
        public string $methodologySlug,
        public ComposeResult $composeResult,
        public ?LintResult $lintBefore = null,
        public ?LintResult $lintAfter = null,
        public array $fixesApplied = [],
        public ?string $clientHandle = null,
        public ?string $notes = null,
        public ?string $exportPath = null,
        public string $createdBy = 'motor-v2-sprint-5',
    ) {
    }
}
