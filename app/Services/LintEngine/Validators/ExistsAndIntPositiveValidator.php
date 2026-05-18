<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * El path debe existir y ser un entero positivo.
 *
 * Usado por: schema_nutr_missing_objetivo_cal (objetivo_cal debe ser int > 0).
 */
final class ExistsAndIntPositiveValidator extends BaseValidator
{
    public function name(): string
    {
        return 'exists_and_int_positive';
    }

    public function check(LintContext $ctx): array
    {
        $path = $ctx->checkDefinition['json_path'] ?? null;
        if (! is_string($path)) {
            return [];
        }

        $matches = $this->resolvePath($ctx->plan, $path);
        if ($matches === []) {
            return [$this->makeViolation($ctx, $path, "Falta el campo `$path`.", null)];
        }

        $violations = [];
        foreach ($matches as $match) {
            $value = $match->value;
            $isInt = is_int($value) || (is_string($value) && ctype_digit($value));
            $intValue = is_int($value) ? $value : (is_string($value) && ctype_digit($value) ? (int) $value : null);

            if (! $isInt || $intValue === null || $intValue <= 0) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $match->path,
                    "El campo `{$match->path}` debe ser un entero positivo.",
                    $value,
                );
            }
        }
        return $violations;
    }
}
