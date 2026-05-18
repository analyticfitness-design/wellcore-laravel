<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ComposeEngine\ComposeEngine;
use App\Services\DecisionEngine\Data\ClientProfile;
use App\Services\DecisionEngine\DecisionEngine;
use App\Services\LintEngine\AutoFixEngine;
use App\Services\LintEngine\LintEngine;
use App\Services\PersistEngine\Data\PersistInput;
use App\Services\PersistEngine\PersistService;
use Illuminate\Console\Command;
use JsonException;
use Throwable;

/**
 * plan:persist — pipeline E2E del motor v2 (Stage 1-5).
 *
 * Flujo:
 *   INTAKE (profile) → SELECT (DecisionEngine) → COMPOSE (ComposeEngine) →
 *   VALIDATE (LintEngine) → AUTO-FIX (AutoFixEngine) → RE-VALIDATE → PERSIST →
 *   (opt) EXPORT JSON.
 *
 * Cada corrida graba un audit row en wellcore_kb.composed_plans con:
 *   - profile, methodology, plan_json
 *   - lint pre + post fix, fixes aplicados
 *   - status final (validated | rejected | exported)
 *
 * NO escribe a producción. Para subir a producción se usa --export y sube
 * manualmente el archivo resultante.
 *
 * Modos similares a plan:compose: --auto / --methodology / --file / --inline / flags.
 */
final class PlanPersistCommand extends Command
{
    protected $signature = 'plan:persist
                            {--file= : Path a JSON con ClientProfile}
                            {--inline= : ClientProfile inline JSON}
                            {--vertical= : profile.vertical (sprint 5 solo entrenamiento)}
                            {--goal= : profile.goal}
                            {--level= : profile.level}
                            {--days= : profile.days (int)}
                            {--gender= : profile.gender}
                            {--tier= : profile.tier (trial|esencial|metodo|elite|rise)}
                            {--equipment=gym_completo : equipo disponible}
                            {--methodology= : slug methodology explícita}
                            {--auto : usa DecisionEngine top-1}
                            {--client-handle= : identificador del cliente para audit (NO subir a prod automático)}
                            {--coach-name= : nombre coach}
                            {--fecha-inicio= : YYYY-MM-DD}
                            {--no-fix : NO aplicar auto-fixes}
                            {--export= : exporta el plan a este path}
                            {--notes= : nota libre para audit}
                            {--json : output JSON del audit row}';

    protected $description = 'Pipeline E2E del motor v2: profile → select → compose → lint → fix → persist (audit en wellcore_kb).';

    public function handle(
        DecisionEngine $decision,
        ComposeEngine $compose,
        LintEngine $lint,
        AutoFixEngine $autoFix,
        PersistService $persist,
    ): int {
        $profile = $this->buildProfile();
        if ($profile === null) {
            return 2;
        }
        if (! in_array($profile->vertical, ['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo'], true)) {
            $this->error("Verticales soportadas: entrenamiento, nutricion, suplementacion, habitos, ciclo. Recibido: '{$profile->vertical}'");
            return 2;
        }

        // === Stage 2 SELECT ===
        $methodologySlug = (string) ($this->option('methodology') ?? '');
        if ($methodologySlug === '' && $this->option('auto')) {
            $selectResult = $decision->decide($profile);
            $recs = $selectResult->byVertical[$profile->vertical] ?? [];
            if ($recs === []) {
                $this->error("DecisionEngine no encontró methodology para vertical={$profile->vertical}.");
                return 1;
            }
            $methodologySlug = $recs[0]->methodologySlug;
            $this->info("[SELECT] methodology: $methodologySlug (confidence {$recs[0]->confidence})");
        }
        if ($methodologySlug === '') {
            $this->error('Falta --methodology=<slug> o --auto');
            return 2;
        }

        // === Stage 3 COMPOSE ===
        $fechaInicio = (string) ($this->option('fecha-inicio') ?: now()->addDay()->toDateString());
        $clientHandle = $this->option('client-handle');
        $coachName = $this->option('coach-name');
        $equipment = array_map('trim', explode(',', (string) ($this->option('equipment') ?: 'gym_completo')));

        try {
            $composeResult = $compose->composeForMethodology(
                $profile,
                $methodologySlug,
                $fechaInicio,
                $clientHandle,
                $coachName,
                $equipment,
            );
        } catch (Throwable $e) {
            $this->error('[COMPOSE] falló: ' . $e->getMessage());
            return 2;
        }
        $this->info(sprintf('[COMPOSE] %.2f ms · warnings: %d', $composeResult->durationMs, count($composeResult->warnings)));

        // === Stage 4 VALIDATE (pre-fix) ===
        $lintBefore = $lint->lint($composeResult->planJson, $profile->vertical);
        $sumPre = $lintBefore->summary();
        $this->info(sprintf(
            '[LINT pre]  errors=%d · warnings=%d · infos=%d · %.2f ms',
            $sumPre['errors'], $sumPre['warnings'], $sumPre['infos'], $sumPre['duration_ms']
        ));

        // === Stage 4.5 AUTO-FIX ===
        $fixesApplied = [];
        $planAfterFix = $composeResult->planJson;
        $lintAfter = $lintBefore;
        if (! $this->option('no-fix') && count($lintBefore->violations) > 0) {
            $fixResult = $autoFix->applyAll($composeResult->planJson, $lintBefore->violations);
            $fixesApplied = $fixResult->appliedFixes;
            $planAfterFix = $fixResult->fixedPlan;
            $this->info(sprintf(
                '[FIX] applied=%d · skipped=%d · failed=%d · %.2f ms',
                count($fixesApplied), $fixResult->skipped, $fixResult->failed, $fixResult->durationMs
            ));

            // === Stage 6 VERIFY (re-lint post fix) ===
            $lintAfter = $lint->lint($planAfterFix, $profile->vertical);
            $sumPost = $lintAfter->summary();
            $this->info(sprintf(
                '[LINT post] errors=%d · warnings=%d · infos=%d · %.2f ms',
                $sumPost['errors'], $sumPost['warnings'], $sumPost['infos'], $sumPost['duration_ms']
            ));
        }

        // === EXPORT (opcional) ===
        $exportPath = null;
        if ($this->option('export')) {
            $exportPath = (string) $this->option('export');
            file_put_contents($exportPath, json_encode($planAfterFix, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $this->info("[EXPORT] $exportPath");
        }

        // === Stage 5 PERSIST ===
        // Reconstruimos un ComposeResult con el plan post-fix (mismo durationMs, warnings idem).
        $composeResultPost = new \App\Services\ComposeEngine\Data\ComposeResult(
            planJson: $planAfterFix,
            warnings: $composeResult->warnings,
            durationMs: $composeResult->durationMs,
        );

        $audit = $persist->persist(new PersistInput(
            profile: $profile,
            methodologySlug: $methodologySlug,
            composeResult: $composeResultPost,
            lintBefore: $lintBefore,
            lintAfter: $lintAfter,
            fixesApplied: $fixesApplied,
            clientHandle: $clientHandle,
            notes: $this->option('notes'),
            exportPath: $exportPath,
        ));

        $this->info(sprintf('[PERSIST] composed_plans #%d · status=%s', $audit->id, $audit->status));

        if ($this->option('json')) {
            $this->line(json_encode([
                'audit_id' => $audit->id,
                'status' => $audit->status,
                'violations_before' => $audit->violations_before,
                'violations_after' => $audit->violations_after,
                'methodology_slug' => $audit->methodology_slug,
                'export_path' => $audit->export_path,
                'created_at' => $audit->created_at?->toIso8601String(),
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        return $audit->status === 'rejected' ? 1 : 0;
    }

    private function buildProfile(): ?ClientProfile
    {
        if ($file = $this->option('file')) {
            if (! is_file($file)) {
                $this->error("Archivo no encontrado: $file");
                return null;
            }
            try {
                $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $this->error('JSON inválido: ' . $e->getMessage());
                return null;
            }
            return is_array($data) ? ClientProfile::fromArray($data) : null;
        }

        if ($inline = $this->option('inline')) {
            try {
                $data = json_decode((string) $inline, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $this->error('JSON inline inválido: ' . $e->getMessage());
                return null;
            }
            return is_array($data) ? ClientProfile::fromArray($data) : null;
        }

        $vertical = $this->option('vertical');
        if (! $vertical) {
            $this->error('Profile requerido: --file, --inline o --vertical + flags.');
            return null;
        }

        return new ClientProfile(
            vertical: (string) $vertical,
            goal: $this->option('goal'),
            level: $this->option('level'),
            days: $this->option('days') !== null ? (int) $this->option('days') : null,
            gender: $this->option('gender'),
            tier: $this->option('tier'),
        );
    }
}
