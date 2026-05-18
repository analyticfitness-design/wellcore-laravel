<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Fixers;

use App\Services\LintEngine\Data\AppliedFix;
use App\Services\LintEngine\Data\FixContext;

/**
 * Aplica una tabla de reemplazos sobre un string en path.
 *
 * Usado por:
 *   - heur_voz_castellano_peninsular (vosotros→ustedes, habéis→han, etc.)
 *   - heur_voz_usted (usted→tú, su plan→tu plan, etc.)
 *
 * El auto_fix.replacements es un map `pattern => replacement`. Cada pattern
 * se interpreta como literal (no regex) con word-boundary opcional.
 */
final class RegexReplaceTableFixer extends BaseFixer
{
    public function name(): string
    {
        return 'regex_replace_table';
    }

    public function apply(FixContext $ctx): ?AppliedFix
    {
        $replacements = $ctx->autoFixDefinition['replacements'] ?? null;
        if (! is_array($replacements) || $replacements === []) {
            return null;
        }

        $path = $ctx->violation->jsonPath;
        $current = $this->mutator->getAtPath($ctx->plan, $path);
        if (! is_string($current)) {
            return null;
        }

        $modified = $current;
        $appliedReplacements = [];

        foreach ($replacements as $pattern => $replacement) {
            if (! is_string($pattern) || ! is_string($replacement)) continue;

            // Word-boundary regex case-insensitive con preservación de capitalización inicial
            $regex = '~\b' . preg_quote($pattern, '~') . '\b~iu';
            $beforeCount = preg_match_all($regex, $modified);
            if ($beforeCount === false || $beforeCount === 0) continue;

            // Para preservar capitalización del primer carácter:
            //   "Usted" → "Tú"  (cap original respetada)
            //   "usted" → "tú"
            $modified = preg_replace_callback($regex, function ($m) use ($replacement) {
                $matched = $m[0];
                if ($matched === '' || ! function_exists('mb_strtoupper')) return $replacement;
                $firstCharOriginal = mb_substr($matched, 0, 1);
                $isUpper = mb_strtoupper($firstCharOriginal) === $firstCharOriginal && mb_strtolower($firstCharOriginal) !== $firstCharOriginal;
                if ($isUpper && mb_strlen($replacement) > 0) {
                    return mb_strtoupper(mb_substr($replacement, 0, 1)) . mb_substr($replacement, 1);
                }
                return $replacement;
            }, $modified);

            $appliedReplacements[$pattern] = $replacement;
        }

        if ($modified === $current) {
            return null;
        }

        $fixedPlan = $this->mutator->setAtPath($ctx->plan, $path, $modified);
        return $this->makeApplied(
            $ctx,
            before: $current,
            after: $modified,
            summary: 'Reemplazos aplicados: ' . json_encode($appliedReplacements, JSON_UNESCAPED_UNICODE),
            fixedPlan: $fixedPlan,
        );
    }
}
