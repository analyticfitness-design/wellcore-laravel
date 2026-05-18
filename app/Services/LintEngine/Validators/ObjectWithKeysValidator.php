<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * El path debe existir Y ser un objeto Y tener AL MENOS UNA de las required_keys_any_of.
 *
 * Usado por: schema_train_missing_split (split{} debe tener Lunes/Martes/.../Viernes).
 */
final class ObjectWithKeysValidator extends BaseValidator
{
    public function name(): string
    {
        return 'object_with_keys';
    }

    public function check(LintContext $ctx): array
    {
        $path = $ctx->checkDefinition['json_path'] ?? null;
        $requiredAnyOf = $ctx->checkDefinition['required_keys_any_of'] ?? [];

        if (! is_string($path) || ! is_array($requiredAnyOf) || $requiredAnyOf === []) {
            return [];
        }

        $matches = $this->resolvePath($ctx->plan, $path);
        if ($matches === []) {
            return [$this->makeViolation(
                $ctx,
                $path,
                "Falta el objeto en `$path`. Debe contener al menos una key de: " . implode(', ', $requiredAnyOf),
                null,
            )];
        }

        $violations = [];
        foreach ($matches as $match) {
            if (! is_array($match->value)) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $match->path,
                    "Se esperaba un objeto en `{$match->path}`, se encontró: " . gettype($match->value),
                    $match->value,
                );
                continue;
            }

            $keys = array_keys($match->value);
            $found = array_intersect($requiredAnyOf, $keys);
            if ($found === []) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $match->path,
                    "El objeto en `{$match->path}` no tiene ninguna de las keys requeridas: " . implode(', ', $requiredAnyOf),
                    $keys,
                );
            }
        }
        return $violations;
    }
}
