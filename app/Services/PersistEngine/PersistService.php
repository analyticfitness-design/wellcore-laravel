<?php

declare(strict_types=1);

namespace App\Services\PersistEngine;

use App\Models\Kb\ComposedPlan;
use App\Services\LintEngine\Data\AppliedFix;
use App\Services\LintEngine\Data\LintResult;
use App\Services\LintEngine\Data\Violation;
use App\Services\PersistEngine\Data\PersistInput;
use Throwable;

/**
 * Stage 5 PERSIST del motor v2.
 *
 * Graba un audit row en wellcore_kb.composed_plans con:
 *   - input (profile, methodology)
 *   - output (plan_json del ComposeEngine)
 *   - calidad (lint pre + post fix, fixes aplicados)
 *   - meta (duración, status)
 *
 * NO escribe a producción wellcore_fitness. Para subir a producción, el usuario
 * exporta el JSON desde acá (campo export_path) y lo sube manualmente.
 *
 * Status derivation:
 *   - 'composed': lint never ran o no había fixes
 *   - 'validated': lint passes después de fix (0 errors)
 *   - 'exported': se generó archivo de export
 *   - 'rejected': lint sigue con errors post-fix
 */
final class PersistService
{
    public function persist(PersistInput $input): ComposedPlan
    {
        $status = $this->deriveStatus($input);

        $row = ComposedPlan::create([
            'client_handle' => $input->clientHandle,
            'plan_type' => (string) ($input->composeResult->planJson['plan_type'] ?? 'entrenamiento'),
            'methodology_slug' => $input->methodologySlug,
            'profile_json' => $input->profile->toArray(),
            'plan_json' => json_encode($input->composeResult->planJson, JSON_UNESCAPED_UNICODE),
            'lint_result_pre_json' => $this->lintToArray($input->lintBefore),
            'lint_result_post_json' => $this->lintToArray($input->lintAfter),
            'fixes_applied_json' => $this->fixesToArray($input->fixesApplied),
            'violations_before' => $input->lintBefore !== null ? count($input->lintBefore->violations) : 0,
            'violations_after' => $input->lintAfter !== null ? count($input->lintAfter->violations) : 0,
            'status' => $status,
            'export_path' => $input->exportPath,
            'notes' => $input->notes,
            'compose_duration_ms' => $input->composeResult->durationMs,
            'lint_duration_ms' => $input->lintAfter?->durationMs ?? $input->lintBefore?->durationMs,
            'created_by' => $input->createdBy,
        ]);

        try {
            $row->refresh();
        } catch (Throwable) {
            // refresh es nice-to-have, no bloquear si falla
        }

        return $row;
    }

    private function deriveStatus(PersistInput $input): string
    {
        if ($input->exportPath !== null) {
            // Si hay export path, asumir que pasó por todos los stages
            return $input->lintAfter !== null && ! $input->lintAfter->passes() ? 'rejected' : 'exported';
        }
        if ($input->lintAfter !== null) {
            return $input->lintAfter->passes() ? 'validated' : 'rejected';
        }
        if ($input->lintBefore !== null) {
            return $input->lintBefore->passes() ? 'validated' : 'composed';
        }
        return 'composed';
    }

    private function lintToArray(?LintResult $result): ?array
    {
        if ($result === null) {
            return null;
        }
        return [
            'summary' => $result->summary(),
            'violations' => array_map(fn (Violation $v) => $v->toArray(), $result->violations),
        ];
    }

    /**
     * @param AppliedFix[] $fixes
     */
    private function fixesToArray(array $fixes): ?array
    {
        if ($fixes === []) {
            return null;
        }
        return array_map(fn (AppliedFix $f) => $f->toArray(), $fixes);
    }
}
