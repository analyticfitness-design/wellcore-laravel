<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * El path debe resolverse a un array con al menos 1 elemento.
 *
 * Usado por: schema_supl_missing_array (suplementos[] debe tener >=1).
 */
final class ArrayNonEmptyValidator extends BaseValidator
{
    public function name(): string
    {
        return 'array_non_empty';
    }

    public function check(LintContext $ctx): array
    {
        $path = $ctx->checkDefinition['json_path'] ?? null;
        if (! is_string($path)) {
            return [];
        }

        $matches = $this->resolvePath($ctx->plan, $path);
        if ($matches === []) {
            return [$this->makeViolation($ctx, $path, "Falta el array en `$path`.", null)];
        }

        $violations = [];
        foreach ($matches as $m) {
            if (! is_array($m->value)) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $m->path,
                    "Se esperaba array en `{$m->path}`, se encontró " . gettype($m->value),
                    $m->value,
                );
                continue;
            }
            if (count($m->value) === 0) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $m->path,
                    "El array en `{$m->path}` está vacío.",
                    [],
                );
            }
        }
        return $violations;
    }
}
