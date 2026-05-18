<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Fixers;

use App\Services\LintEngine\Data\AppliedFix;
use App\Services\LintEngine\Data\FixContext;

/**
 * Renombra keys en un objeto según auto_fix.mapping.
 *
 * Usado por:
 *   - schema_nutr_invalid_macros_keys_with_g (proteina_g → proteina, etc.)
 *   - schema_supl_uses_timing_instead_momento (timing → momento)
 *
 * El violation.jsonPath apunta al objeto que contiene las keys a renombrar.
 */
final class RenameKeysFixer extends BaseFixer
{
    public function name(): string
    {
        return 'rename_keys';
    }

    public function apply(FixContext $ctx): ?AppliedFix
    {
        $mapping = $ctx->autoFixDefinition['mapping'] ?? null;
        if (! is_array($mapping) || $mapping === []) {
            return null;
        }

        $path = $ctx->violation->jsonPath;
        $current = $this->mutator->getAtPath($ctx->plan, $path);
        if (! is_array($current)) {
            return null;
        }

        $renamed = [];
        $changes = [];
        foreach ($current as $key => $value) {
            $newKey = $mapping[$key] ?? $key;
            $renamed[$newKey] = $value;
            if ($newKey !== $key) {
                $changes[$key] = $newKey;
            }
        }

        if ($changes === []) {
            return null;
        }

        $fixedPlan = $this->mutator->setAtPath($ctx->plan, $path, $renamed);
        return $this->makeApplied(
            $ctx,
            before: array_keys($current),
            after: array_keys($renamed),
            summary: 'Keys renombradas: ' . json_encode($changes, JSON_UNESCAPED_UNICODE),
            fixedPlan: $fixedPlan,
        );
    }
}
