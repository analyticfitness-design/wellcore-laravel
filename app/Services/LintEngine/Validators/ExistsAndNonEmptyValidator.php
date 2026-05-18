<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * El path debe existir Y tener valor no vacío.
 *
 * Usado por rules como: schema_train_missing_objetivo, schema_nutr_missing_objetivo.
 */
final class ExistsAndNonEmptyValidator extends BaseValidator
{
    public function name(): string
    {
        return 'exists_and_non_empty';
    }

    public function check(LintContext $ctx): array
    {
        $path = $ctx->checkDefinition['json_path'] ?? null;
        if (! is_string($path)) {
            return [];
        }

        $matches = $this->resolvePath($ctx->plan, $path);

        if ($matches === []) {
            return [$this->makeViolation($ctx, $path, "Falta el campo en el path `$path`.", null)];
        }

        $violations = [];
        foreach ($matches as $match) {
            if (! $match->exists || $this->isEmpty($match->value)) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $match->path,
                    "El campo `{$match->path}` existe pero está vacío.",
                    $match->value,
                );
            }
        }
        return $violations;
    }
}
