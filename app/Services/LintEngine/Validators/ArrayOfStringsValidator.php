<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * El valor debe ser un array donde TODOS los elementos son strings.
 *
 * Usado por: schema_nutr_invalid_opciones_shape (comidas[].opcion_a debe ser
 * array de strings, no array de objetos {item, cantidad}).
 */
final class ArrayOfStringsValidator extends BaseValidator
{
    public function name(): string
    {
        return 'array_of_strings';
    }

    public function check(LintContext $ctx): array
    {
        $path = $ctx->checkDefinition['json_path'] ?? null;
        if (! is_string($path)) {
            return [];
        }

        $matches = $this->resolvePath($ctx->plan, $path);
        $violations = [];

        foreach ($matches as $m) {
            if (! is_array($m->value)) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $m->path,
                    "Se esperaba array de strings en `{$m->path}`, se encontró " . gettype($m->value),
                    $m->value,
                );
                continue;
            }

            $nonStrings = [];
            foreach ($m->value as $idx => $el) {
                if (! is_string($el)) {
                    $nonStrings[] = $idx;
                }
            }

            if ($nonStrings !== []) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $m->path,
                    "El array en `{$m->path}` tiene elementos no-string en índices: " . implode(', ', $nonStrings),
                    $m->value,
                );
            }
        }
        return $violations;
    }
}
