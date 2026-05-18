<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * El valor debe ser exactamente uno de los allowed_values (no prefijo, no fuzzy).
 *
 * Usado por: sql_plan_type_not_in_enum (defense-in-depth contra ENUM permisivo).
 */
final class AllowedValuesValidator extends BaseValidator
{
    public function name(): string
    {
        return 'allowed_values';
    }

    public function check(LintContext $ctx): array
    {
        $path = $ctx->checkDefinition['json_path'] ?? null;
        $allowed = $ctx->checkDefinition['allowed_values'] ?? [];

        if (! is_string($path) || ! is_array($allowed) || $allowed === []) {
            return [];
        }

        $matches = $this->resolvePath($ctx->plan, $path);
        if ($matches === []) {
            return [$this->makeViolation($ctx, $path, "Falta el campo `$path`.", null)];
        }

        $violations = [];
        foreach ($matches as $m) {
            if (! in_array($m->value, $allowed, true)) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $m->path,
                    "Valor no permitido en `{$m->path}`. Allowed: " . implode(' | ', $allowed) . ". Encontrado: " . var_export($m->value, true),
                    $m->value,
                );
            }
        }
        return $violations;
    }
}
