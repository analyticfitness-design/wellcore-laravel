<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Fixers;

use App\Services\LintEngine\Data\AppliedFix;
use App\Services\LintEngine\Data\FixContext;

/**
 * Reescribe URLs cambiando el prefijo de dominio.
 *
 * Usado por: external_gif_url_pattern_wrong
 *   "https://wellcorefitness.com/storage/exercises/press-banca.gif"
 *   → "https://raw.githubusercontent.com/.../master/press-banca.gif"
 *
 * Parámetros:
 *   - from_pattern: regex que matchea el prefijo a remover (anclado al inicio)
 *   - to_prefix: string que se concatena con el resto de la URL después del match
 */
final class RewriteDomainFixer extends BaseFixer
{
    public function name(): string
    {
        return 'rewrite_domain';
    }

    public function apply(FixContext $ctx): ?AppliedFix
    {
        $fromPattern = $ctx->autoFixDefinition['from_pattern'] ?? null;
        $toPrefix = $ctx->autoFixDefinition['to_prefix'] ?? null;

        if (! is_string($fromPattern) || ! is_string($toPrefix)) {
            return null;
        }

        $path = $ctx->violation->jsonPath;
        $current = $this->mutator->getAtPath($ctx->plan, $path);
        if (! is_string($current)) {
            return null;
        }

        // Delimitador `~` para no colisionar con `/` en URLs
        $regex = '~' . str_replace('~', '\\~', $fromPattern) . '~u';
        $rewritten = @preg_replace($regex, $toPrefix, $current, 1);

        if ($rewritten === null || $rewritten === $current) {
            return null;
        }

        $fixedPlan = $this->mutator->setAtPath($ctx->plan, $path, $rewritten);
        return $this->makeApplied(
            $ctx,
            before: $current,
            after: $rewritten,
            summary: "URL reescrita: \"$current\" → \"$rewritten\"",
            fixedPlan: $fixedPlan,
        );
    }
}
