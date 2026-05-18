<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Cada URL del path debe matchear expected_pattern (regex).
 *
 * Usado por: external_gif_url_pattern_wrong (todas las gif_url deben empezar
 * con https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/).
 *
 * NO hace HTTP — solo valida pattern. ExternalHeadValidator hace el HEAD real.
 */
final class UrlMatchesPatternValidator extends BaseValidator
{
    public function name(): string
    {
        return 'url_matches_pattern';
    }

    public function check(LintContext $ctx): array
    {
        $path = $ctx->checkDefinition['json_path'] ?? null;
        $pattern = $ctx->checkDefinition['expected_pattern'] ?? null;

        if (! is_string($path) || ! is_string($pattern)) {
            return [];
        }

        // Usamos `~` como delimitador para no colisionar con `/` en URLs
        $regex = '~' . str_replace('~', '\\~', $pattern) . '~u';
        $matches = $this->resolvePath($ctx->plan, $path);
        $violations = [];

        foreach ($matches as $m) {
            $url = $m->value;
            if (! is_string($url)) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $m->path,
                    "Se esperaba URL string en `{$m->path}`, se encontró " . gettype($url),
                    $url,
                );
                continue;
            }
            if (@preg_match($regex, $url) !== 1) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $m->path,
                    "URL en `{$m->path}` no matchea el patrón esperado.",
                    $url,
                );
            }
        }
        return $violations;
    }
}
