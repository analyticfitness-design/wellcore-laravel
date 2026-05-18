<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Cada match del path debe existir y ser no-vacío.
 *
 * Diferencia con exists_and_non_empty: itera sobre wildcards.
 * Usado por: schema_train_missing_phase_field ($.semanas[*].fase debe estar en CADA semana).
 *
 * El validator confía en que el JsonPathResolver retorna 0 matches si el path
 * con wildcard no se resuelve. Para checks por-elemento del array padre, hay
 * que mirar el array padre.
 */
final class ExistsInEachValidator extends BaseValidator
{
    public function name(): string
    {
        return 'exists_in_each';
    }

    public function check(LintContext $ctx): array
    {
        $path = $ctx->checkDefinition['json_path'] ?? null;
        if (! is_string($path)) {
            return [];
        }

        // Para "$.semanas[*].fase", inferimos el array padre "$.semanas"
        // dividiendo en la última ocurrencia de [*]
        $arrayPath = $this->extractArrayPath($path);
        $childKey = $this->extractChildKey($path);

        if ($arrayPath === null || $childKey === null) {
            // Sin parseo claro: fallback a comportamiento de exists_and_non_empty
            $matches = $this->resolvePath($ctx->plan, $path);
            if ($matches === []) {
                return [$this->makeViolation($ctx, $path, "Path `$path` no se resolvió en el documento.", null)];
            }
            $violations = [];
            foreach ($matches as $m) {
                if ($this->isEmpty($m->value)) {
                    $violations[] = $this->makeViolation($ctx, $m->path, "Campo vacío en `{$m->path}`.", $m->value);
                }
            }
            return $violations;
        }

        $arrayMatches = $this->resolvePath($ctx->plan, $arrayPath);
        if ($arrayMatches === []) {
            // El array padre no existe — otra rule cubre eso
            return [];
        }

        $violations = [];
        foreach ($arrayMatches as $arrMatch) {
            if (! is_array($arrMatch->value)) {
                continue;
            }
            foreach ($arrMatch->value as $idx => $item) {
                $itemPath = $arrMatch->path . "[$idx]";
                if (! is_array($item) || ! array_key_exists($childKey, $item) || $this->isEmpty($item[$childKey])) {
                    $foundValue = is_array($item) ? ($item[$childKey] ?? null) : null;
                    $violations[] = $this->makeViolation(
                        $ctx,
                        "$itemPath.$childKey",
                        "Falta o vacío `{$childKey}` en `$itemPath`.",
                        $foundValue,
                    );
                }
            }
        }
        return $violations;
    }

    /**
     * "$.semanas[*].fase" → "$.semanas"
     * "$.semanas[*].dias[*].fase" → "$.semanas[*].dias"
     */
    private function extractArrayPath(string $path): ?string
    {
        $lastWildcard = strrpos($path, '[*]');
        if ($lastWildcard === false) {
            return null;
        }
        return substr($path, 0, $lastWildcard);
    }

    /**
     * "$.semanas[*].fase" → "fase"
     * "$.semanas[*].dias[*]" → null (no hay key después del último [*])
     */
    private function extractChildKey(string $path): ?string
    {
        $lastWildcard = strrpos($path, '[*]');
        if ($lastWildcard === false) {
            return null;
        }
        $tail = substr($path, $lastWildcard + 3); // skip "[*]"
        if ($tail === '' || $tail === false) {
            return null;
        }
        // tail empieza con "." o similar
        $key = ltrim($tail, '.');
        // Si hay más segmentos, tomar solo el primero
        $key = explode('.', $key)[0] ?? null;
        $key = $key === null ? null : (explode('[', $key)[0] ?? null);
        return $key === '' ? null : $key;
    }
}
