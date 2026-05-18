<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Fixers;

use App\Services\LintEngine\Data\AppliedFix;
use App\Services\LintEngine\Data\FixContext;

/**
 * Remueve oraciones completas que contengan algún patrón regex del check.
 *
 * Usado por: heur_mention_of_ia
 *   Si la nota contiene "Este plan fue generado por IA con Claude", esa oración
 *   completa se elimina del string.
 *
 * Detecta oraciones por punto seguido de espacio o final de string.
 * Los patrones a buscar vienen del check_definition.patterns (heredados).
 *
 * Como el FixContext no incluye check_definition completo, recibimos los
 * patterns desde auto_fix.triggers si el seed los duplicó allí; sino fallback
 * a heurística común (palabras AI, Claude, Anthropic).
 */
final class RemoveSentenceContainingTriggerFixer extends BaseFixer
{
    public function name(): string
    {
        return 'remove_sentence_containing_trigger';
    }

    public function apply(FixContext $ctx): ?AppliedFix
    {
        // Fallback triggers si no están en auto_fix.triggers
        $defaultTriggers = ['\\bIA\\b', '\\bClaude\\b', '\\bAnthropic\\b', 'generad[oa]\\s+por', 'inteligencia\\s+artificial', 'generated\\s+by\\s+AI'];
        $triggers = $ctx->autoFixDefinition['triggers'] ?? $defaultTriggers;

        if (! is_array($triggers) || $triggers === []) {
            return null;
        }

        $path = $ctx->violation->jsonPath;
        $current = $this->mutator->getAtPath($ctx->plan, $path);
        if (! is_string($current)) {
            return null;
        }

        // Split por punto seguido de espacio o fin de string
        // Conservamos el delimitador
        $sentences = preg_split('/(?<=[.!?])\s+/u', $current, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $keep = [];
        $removed = [];

        foreach ($sentences as $sentence) {
            $shouldRemove = false;
            foreach ($triggers as $trigger) {
                if (! is_string($trigger)) continue;
                $regex = '~' . str_replace('~', '\\~', $trigger) . '~iu';
                if (@preg_match($regex, $sentence) === 1) {
                    $shouldRemove = true;
                    break;
                }
            }
            if ($shouldRemove) {
                $removed[] = $sentence;
            } else {
                $keep[] = $sentence;
            }
        }

        if ($removed === []) {
            return null;
        }

        $modified = trim(implode(' ', $keep));
        $fixedPlan = $this->mutator->setAtPath($ctx->plan, $path, $modified);

        return $this->makeApplied(
            $ctx,
            before: $current,
            after: $modified,
            summary: 'Oraciones removidas: ' . count($removed) . ' (' . mb_substr(implode(' | ', $removed), 0, 100) . ')',
            fixedPlan: $fixedPlan,
        );
    }
}
