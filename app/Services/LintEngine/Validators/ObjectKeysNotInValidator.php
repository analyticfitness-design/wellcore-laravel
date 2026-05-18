<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * El objeto en path NO debe tener ninguna de las forbidden_keys.
 *
 * Usado por:
 *  - schema_nutr_invalid_macros_keys_with_g (no _g en comidas.macros)
 *  - schema_supl_uses_timing_instead_momento (timing → momento)
 */
final class ObjectKeysNotInValidator extends BaseValidator
{
    public function name(): string
    {
        return 'object_keys_not_in';
    }

    public function check(LintContext $ctx): array
    {
        $path = $ctx->checkDefinition['json_path'] ?? null;
        $forbidden = $ctx->checkDefinition['forbidden_keys'] ?? [];

        if (! is_string($path) || ! is_array($forbidden) || $forbidden === []) {
            return [];
        }

        $matches = $this->resolvePath($ctx->plan, $path);
        $violations = [];

        foreach ($matches as $match) {
            if (! is_array($match->value)) {
                continue;
            }
            $keys = array_keys($match->value);
            $present = array_intersect($forbidden, $keys);
            if ($present !== []) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $match->path,
                    "El objeto en `{$match->path}` contiene key(s) prohibida(s): " . implode(', ', $present),
                    $present,
                );
            }
        }
        return $violations;
    }
}
