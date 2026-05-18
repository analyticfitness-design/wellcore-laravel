<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
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
 * plan:batch — procesa N clientes desde JSON o CSV, corriendo plan:bundle por cada uno.
 *
 * Input JSON shape esperado:
 *   [
 *     {
 *       "client_handle": "Cliente A",
 *       "goal": "hipertrofia",
 *       "level": "intermedio",
 *       "days": 5,
 *       "gender": "F",
 *       "tier": "elite",
 *       "coach_name": "Daniel"
 *     },
 *     { ... otro cliente ... }
 *   ]
 *
 * Input CSV shape esperado (header obligatorio):
 *   client_handle,goal,level,days,gender,tier,coach_name
 *   "Cliente A",hipertrofia,intermedio,5,F,elite,Daniel
 *
 * Output: tabla con resumen por cliente (composed_ids, verticales generados, errors).
 *
 * Modos:
 *   --file=clientes.json    (auto-detect JSON o CSV por extensión)
 *   --fecha-inicio=YYYY-MM-DD  (default: mañana)
 *   --skip=ciclo               (excluye verticales en todos los clientes)
 *   --json                     (output JSON estructurado)
 */
final class PlanBatchCommand extends Command
{
    private const ALWAYS_APPLICABLE = ['entrenamiento', 'nutricion', 'suplementacion', 'habitos'];
    private const FEMALE_ELITE_ONLY = 'ciclo';

    protected $signature = 'plan:batch
                            {--file= : Path al archivo JSON o CSV con perfiles de clientes}
                            {--fecha-inicio= : YYYY-MM-DD (default mañana)}
                            {--skip= : verticales a excluir (CSV)}
                            {--no-fix : omitir auto-fix}
                            {--json : output JSON estructurado}';

    protected $description = 'Procesa N clientes desde JSON/CSV, generando 3-5 planes por cada uno (plan:bundle).';

    public function handle(
        DecisionEngine $decision,
        ComposeEngine $compose,
        LintEngine $lint,
        AutoFixEngine $autoFix,
        PersistService $persist,
    ): int {
        $file = $this->option('file');
        if (! $file || ! is_file($file)) {
            $this->error('--file es requerido y debe existir.');
            return 2;
        }

        $clients = $this->loadClients($file);
        if ($clients === null) {
            return 2;
        }
        if ($clients === []) {
            $this->warn('Archivo no contiene clientes.');
            return 0;
        }

        $this->info("Procesando " . count($clients) . " clientes...");
        $this->newLine();

        $fechaInicio = (string) ($this->option('fecha-inicio') ?: now()->addDay()->toDateString());
        $applyFix = ! $this->option('no-fix');
        $skipVerticals = $this->option('skip') ? array_map('trim', explode(',', $this->option('skip'))) : [];

        $start = microtime(true);
        $batchResults = [];

        foreach ($clients as $i => $client) {
            $handle = $client['client_handle'] ?? "cliente-$i";
            $verticals = $this->verticalsForClient($client, $skipVerticals);

            $clientResults = [];
            foreach ($verticals as $vertical) {
                $profile = new ClientProfile(
                    vertical: $vertical,
                    goal: $client['goal'] ?? null,
                    level: $client['level'] ?? null,
                    days: isset($client['days']) ? (int) $client['days'] : null,
                    gender: $client['gender'] ?? null,
                    tier: $client['tier'] ?? null,
                );
                $clientResults[] = $this->runVerticalPipeline(
                    $decision, $compose, $lint, $autoFix, $persist,
                    $profile, $fechaInicio, $applyFix,
                    $handle, $client['coach_name'] ?? null,
                );
            }

            $batchResults[] = [
                'client_handle' => $handle,
                'verticals_count' => count($verticals),
                'composed_ids' => array_values(array_filter(array_column($clientResults, 'composed_id'))),
                'validated' => count(array_filter($clientResults, fn ($r) => in_array($r['status'], ['validated', 'exported'], true))),
                'rejected' => count(array_filter($clientResults, fn ($r) => $r['status'] === 'rejected')),
                'duration_ms' => array_sum(array_column($clientResults, 'duration_ms')),
            ];
        }

        $totalDuration = (microtime(true) - $start) * 1000;

        if ($this->option('json')) {
            $this->line(json_encode([
                'total_clients' => count($clients),
                'total_duration_ms' => round($totalDuration, 2),
                'results' => $batchResults,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            $this->renderTable($batchResults, $totalDuration);
        }

        $allValidated = array_reduce($batchResults, fn ($acc, $r) => $acc && $r['rejected'] === 0, true);
        return $allValidated ? 0 : 1;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function loadClients(string $file): ?array
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        try {
            if ($ext === 'json') {
                $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
                if (! is_array($data)) {
                    $this->error('JSON debe ser un array de objetos.');
                    return null;
                }
                return $data;
            }

            if ($ext === 'csv') {
                return $this->parseCsv($file);
            }
        } catch (JsonException $e) {
            $this->error('JSON inválido: ' . $e->getMessage());
            return null;
        } catch (Throwable $e) {
            $this->error('Error al leer archivo: ' . $e->getMessage());
            return null;
        }

        $this->error("Extensión no soportada: $ext (use .json o .csv)");
        return null;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parseCsv(string $file): array
    {
        $rows = [];
        $handle = fopen($file, 'r');
        if (! $handle) {
            return [];
        }
        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return [];
        }
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($header)) {
                $rows[] = array_combine($header, $row);
            }
        }
        fclose($handle);
        return $rows;
    }

    /**
     * @param array<string,mixed> $client
     * @param string[] $skip
     * @return string[]
     */
    private function verticalsForClient(array $client, array $skip): array
    {
        $verticals = self::ALWAYS_APPLICABLE;
        $gender = $client['gender'] ?? null;
        $tier = $client['tier'] ?? null;

        $isFemenino = $gender !== null && in_array(strtolower((string) $gender), ['f', 'femenino', 'female', 'mujer'], true);
        $isElitePlus = in_array($tier, ['elite', 'rise'], true);
        if ($isFemenino && $isElitePlus) {
            $verticals[] = self::FEMALE_ELITE_ONLY;
        }

        if ($skip !== []) {
            $verticals = array_values(array_diff($verticals, $skip));
        }

        return $verticals;
    }

    private function runVerticalPipeline(
        DecisionEngine $decision,
        ComposeEngine $compose,
        LintEngine $lint,
        AutoFixEngine $autoFix,
        PersistService $persist,
        ClientProfile $profile,
        string $fechaInicio,
        bool $applyFix,
        string $clientHandle,
        ?string $coachName,
    ): array {
        $vertical = $profile->vertical;
        $start = microtime(true);

        try {
            $decisionResult = $decision->decide($profile);
            $recs = $decisionResult->byVertical[$vertical] ?? [];
            if ($recs === []) {
                return ['vertical' => $vertical, 'composed_id' => null, 'status' => 'rejected', 'duration_ms' => 0];
            }
            $methodologySlug = $recs[0]->methodologySlug;

            $composeResult = $compose->composeForMethodology(
                $profile, $methodologySlug, $fechaInicio,
                $clientHandle, $coachName, ['gym_completo'],
            );

            $lintBefore = $lint->lint($composeResult->planJson, $vertical);
            $fixesApplied = [];
            $planFinal = $composeResult->planJson;
            $lintAfter = $lintBefore;
            if ($applyFix && count($lintBefore->violations) > 0) {
                $fixResult = $autoFix->applyAll($composeResult->planJson, $lintBefore->violations);
                $fixesApplied = $fixResult->appliedFixes;
                $planFinal = $fixResult->fixedPlan;
                $lintAfter = $lint->lint($planFinal, $vertical);
            }

            $composeResultPost = new \App\Services\ComposeEngine\Data\ComposeResult(
                planJson: $planFinal,
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
                notes: 'batch',
            ));

            return [
                'vertical' => $vertical,
                'composed_id' => $audit->id,
                'status' => $audit->status,
                'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (Throwable $e) {
            return ['vertical' => $vertical, 'composed_id' => null, 'status' => 'rejected', 'duration_ms' => 0, 'error' => $e->getMessage()];
        }
    }

    private function renderTable(array $results, float $totalDuration): void
    {
        $this->info('═══ Batch Pipeline E2E ═══');
        $totalClients = count($results);
        $totalValidated = array_sum(array_column($results, 'validated'));
        $totalRejected = array_sum(array_column($results, 'rejected'));
        $totalIds = array_sum(array_map('count', array_column($results, 'composed_ids')));
        $this->line(sprintf(
            'Clientes: %d · Planes generados: %d · Validated: %d · Rejected: %d · Duración total: %.2f ms',
            $totalClients, $totalIds, $totalValidated, $totalRejected, $totalDuration,
        ));
        $this->newLine();

        $rows = [];
        foreach ($results as $r) {
            $rows[] = [
                $r['client_handle'],
                $r['verticals_count'],
                count($r['composed_ids']),
                $r['validated'] . '/' . ($r['validated'] + $r['rejected']),
                round($r['duration_ms'], 1) . ' ms',
                implode(',', $r['composed_ids']),
            ];
        }
        $this->table(
            ['Client', '# verts', '# planes', 'OK', 'duración', 'composed_ids'],
            $rows,
        );
    }
}
