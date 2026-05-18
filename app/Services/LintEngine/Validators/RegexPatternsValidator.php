<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Busca regex patterns en múltiples json_paths del documento.
 *
 * Usado por: heur_voz_castellano_peninsular, heur_voz_usted, heur_voz_marketing,
 * heur_mention_of_ia.
 *
 * Cada hit produce una violation con el path y el texto matcheado.
 */
final class RegexPatternsValidator extends BaseValidator
{
    public function name(): string
    {
        return 'regex_patterns_in_paths';
    }

    public function check(LintContext $ctx): array
    {
        $paths = $ctx->checkDefinition['json_paths'] ?? [];
        $patterns = $ctx->checkDefinition['patterns'] ?? [];

        if (! is_array($paths) || ! is_array($patterns) || $paths === [] || $patterns === []) {
            return [];
        }

        $violations = [];
        $maxHitsPerPath = 5; // evita explosion en planes con mucho texto

        foreach ($paths as $path) {
            if (! is_string($path)) continue;
            $matches = $this->resolvePath($ctx->plan, $path);
            foreach ($matches as $m) {
                if (! is_string($m->value)) continue;
                $hits = 0;
                foreach ($patterns as $patternDef) {
                    if (! is_array($patternDef) || ! isset($patternDef['regex'])) continue;
                    $regex = '/' . $patternDef['regex'] . '/' . (($patternDef['case_insensitive'] ?? false) ? 'i' : '') . 'u';
                    if (@preg_match($regex, $m->value) !== 1) {
                        continue;
                    }
                    preg_match($regex, $m->value, $found);
                    $violations[] = $this->makeViolation(
                        $ctx,
                        $m->path,
                        "Patrón prohibido detectado en `{$m->path}`: \"" . ($found[0] ?? '') . "\".",
                        $found[0] ?? null,
                    );
                    $hits++;
                    if ($hits >= $maxHitsPerPath) break;
                }
            }
        }
        return $violations;
    }
}
