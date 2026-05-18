<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Cada match del path debe ser un objeto que tenga TODOS los required_keys.
 *
 * Usado por: schema_train_missing_dias_meta (cada dia debe tener dia_semana + grupo_muscular).
 */
final class HasRequiredKeysValidator extends BaseValidator
{
    public function name(): string
    {
        return 'has_required_keys';
    }

    public function check(LintContext $ctx): array
    {
        $path = $ctx->checkDefinition['json_path'] ?? null;
        $required = $ctx->checkDefinition['required_keys'] ?? [];

        if (! is_string($path) || ! is_array($required) || $required === []) {
            return [];
        }

        $matches = $this->resolvePath($ctx->plan, $path);
        if ($matches === []) {
            // No matches: no items en el array — no es violation aquí (otra rule cubre eso)
            return [];
        }

        $violations = [];
        foreach ($matches as $match) {
            if (! is_array($match->value)) {
                continue;
            }
            $keys = array_keys($match->value);
            $missing = array_diff($required, $keys);
            if ($missing !== []) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $match->path,
                    "Falta(n) key(s) requerida(s) en `{$match->path}`: " . implode(', ', $missing),
                    $missing,
                );
            }
        }
        return $violations;
    }
}
