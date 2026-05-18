<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Fixers;

use App\Services\LintEngine\Contracts\FixApplier;
use App\Services\LintEngine\Data\AppliedFix;
use App\Services\LintEngine\Data\FixContext;
use App\Services\LintEngine\JsonPath\PathMutator;

abstract class BaseFixer implements FixApplier
{
    public function __construct(
        protected readonly PathMutator $mutator,
    ) {
    }

    protected function makeApplied(FixContext $ctx, mixed $before, mixed $after, string $summary, array $fixedPlan): AppliedFix
    {
        return new AppliedFix(
            ruleCode: $ctx->rule->code,
            fixerName: $this->name(),
            jsonPath: $ctx->violation->jsonPath,
            before: $before,
            after: $after,
            summary: $summary,
            fixedPlan: $fixedPlan,
        );
    }
}
