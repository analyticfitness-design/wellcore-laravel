<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * El valor (string) debe empezar con UNO de los allowed_values.
 *
 * Usado por: schema_train_invalid_phase_name (fase empieza con un nombre oficial).
 * Soporta el patrón "Adaptación · RIR 3" donde solo el prefijo debe matchear.
 */
final class StartsWithValidator extends BaseValidator
{
    public function name(): string
    {
        return 'startsWith';
    }

    public function check(LintContext $ctx): array
    {
        $path = $ctx->checkDefinition['json_path'] ?? null;
        $allowed = $ctx->checkDefinition['allowed_values'] ?? [];
        $caseSensitive = (bool) ($ctx->checkDefinition['case_sensitive'] ?? true);

        if (! is_string($path) || ! is_array($allowed) || $allowed === []) {
            return [];
        }

        $matches = $this->resolvePath($ctx->plan, $path);
        if ($matches === []) {
            return [];
        }

        $violations = [];
        foreach ($matches as $m) {
            if (! is_string($m->value)) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $m->path,
                    "Se esperaba string en `{$m->path}`, se encontró " . gettype($m->value),
                    $m->value,
                );
                continue;
            }

            $haystack = $caseSensitive ? $m->value : mb_strtolower($m->value);
            $matched = false;
            foreach ($allowed as $candidate) {
                $needle = $caseSensitive ? $candidate : mb_strtolower((string) $candidate);
                if (str_starts_with($haystack, $needle)) {
                    $matched = true;
                    break;
                }
            }

            if (! $matched) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $m->path,
                    "Valor inválido en `{$m->path}`. Debe empezar con: " . implode(' | ', $allowed) . ". Encontrado: \"{$m->value}\".",
                    $m->value,
                );
            }
        }
        return $violations;
    }
}
