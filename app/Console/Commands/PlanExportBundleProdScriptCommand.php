<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use Illuminate\Console\Command;

/**
 * plan:export-bundle-prod-script — versión multi-plan de plan:export-prod-script.
 *
 * Genera UN solo script PHP que inserta 3-5 planes simultáneos (resultado de
 * plan:bundle) en una sola transaction en wellcore_fitness.assigned_plans.
 *
 * Patrón seguro idéntico a plan:export-prod-script:
 *   - DRY_RUN=true por default
 *   - NO conecta a prod desde laptop
 *   - Transaction atómica para los N planes (rollback si alguno falla)
 *   - Verificaciones pre-write (cliente + coach existen)
 *   - UPDATE active=0 de planes previos por plan_type
 *
 * Uso:
 *   php artisan plan:export-bundle-prod-script \
 *       --composed-ids=1,2,3,4,5 \
 *       --client-id=98 \
 *       --coach-id=7 \
 *       --valid-from=2026-06-01
 */
final class PlanExportBundleProdScriptCommand extends Command
{
    protected $signature = 'plan:export-bundle-prod-script
                            {--composed-ids= : lista CSV de wellcore_kb.composed_plans IDs}
                            {--client-id= : client_id real en producción}
                            {--coach-id= : coach_id (assigned_by)}
                            {--valid-from= : YYYY-MM-DD (default = primer plan.fecha_inicio)}
                            {--expires-at= : YYYY-MM-DD (default = valid_from + duracion del primer plan)}
                            {--out= : path output (default = bootstrap/kb-prod/insert_bundle_<timestamp>.php)}';

    protected $description = 'Genera script PHP standalone para subir un bundle de 3-5 planes a producción (transaction atómica).';

    public function handle(): int
    {
        $idsRaw = $this->option('composed-ids');
        $clientId = $this->option('client-id');
        $coachId = $this->option('coach-id');

        if (! $idsRaw || ! $clientId || ! $coachId) {
            $this->error('Requeridos: --composed-ids, --client-id, --coach-id');
            return 2;
        }

        $ids = array_map('intval', array_filter(array_map('trim', explode(',', $idsRaw))));
        if ($ids === []) {
            $this->error('--composed-ids está vacío.');
            return 2;
        }

        $composed = ComposedPlan::whereIn('id', $ids)->orderBy('id')->get();
        if ($composed->count() !== count($ids)) {
            $foundIds = $composed->pluck('id')->toArray();
            $missing = array_diff($ids, $foundIds);
            $this->error('composed_plans no encontrados: ' . implode(', ', $missing));
            return 2;
        }

        // Validar que todos son del mismo client_handle (sanidad)
        $handles = $composed->pluck('client_handle')->unique();
        if ($handles->count() > 1) {
            $this->warn('Los composed_plans tienen client_handles distintos: ' . $handles->implode(', '));
        }

        $firstPlanJson = $composed->first()->planJson();
        $duracionSemanas = (int) ($firstPlanJson['duracion_semanas'] ?? 4);
        $defaultValidFrom = (string) ($firstPlanJson['fecha_inicio'] ?? now()->addDay()->toDateString());

        $validFrom = $this->option('valid-from') ?? $defaultValidFrom;
        $expiresAt = $this->option('expires-at') ?? \Carbon\Carbon::parse($validFrom)->addWeeks($duracionSemanas)->toDateString();

        $timestamp = now()->format('Y-m-d_His');
        $defaultOut = base_path("bootstrap/kb-prod/insert_bundle_{$timestamp}.php");
        $outPath = $this->option('out') ?? $defaultOut;

        $outDir = dirname($outPath);
        if (! is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        $plansData = [];
        foreach ($composed as $cp) {
            $plansData[] = [
                'composed_id' => $cp->id,
                'plan_type' => $cp->plan_type,
                'methodology_slug' => $cp->methodology_slug,
                'plan_json' => $cp->planJson(),
            ];
        }

        $script = $this->buildScript(
            plans: $plansData,
            clientId: (int) $clientId,
            coachId: (int) $coachId,
            validFrom: $validFrom,
            expiresAt: $expiresAt,
        );

        file_put_contents($outPath, $script);

        foreach ($composed as $cp) {
            $cp->update([
                'export_path' => $outPath,
                'notes' => trim(($cp->notes ?? '') . "\n[" . now()->toIso8601String() . "] bundle export → client_id=$clientId"),
            ]);
        }

        $relPath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $outPath);
        $relPath = str_replace('\\', '/', $relPath);

        $this->info("✓ Bundle script generado: $relPath");
        $this->line("  · " . count($plansData) . ' planes incluidos');
        $this->line("  · client_id=$clientId · coach_id=$coachId · vigencia: $validFrom → $expiresAt");
        $this->newLine();
        $this->info('Pasos para subir a producción (1 sola transaction para los N planes):');
        $this->line("1. cat $relPath");
        $this->line('2. git add + commit + push origin main');
        $this->line('3. EasyPanel: silvia-gitpull-load');
        $this->line("4. En container: php /code/$relPath  (DRY_RUN=true por default)");
        $this->line('5. Si dry-run OK: editar archivo, DRY_RUN=false, re-ejecutar');
        $this->line('6. Cache invalidation (el script imprime el comando exacto)');

        return 0;
    }

    /**
     * @param array<int, array{composed_id:int,plan_type:string,methodology_slug:string,plan_json:array}> $plans
     */
    private function buildScript(array $plans, int $clientId, int $coachId, string $validFrom, string $expiresAt): string
    {
        $generatedAt = now()->toIso8601String();
        $plansExport = var_export($plans, true);
        $plansCount = count($plans);
        $planTypesList = implode(', ', array_map(fn ($p) => "'{$p['plan_type']}'", $plans));

        return <<<PHP
<?php

/**
 * insert_bundle.php — generado automáticamente por plan:export-bundle-prod-script
 *
 * Inserta $plansCount planes (bundle multi-vertical) en wellcore_fitness.assigned_plans
 * dentro de UNA SOLA transaction atómica.
 *
 * Generado:    {$generatedAt}
 * Cliente:     {$clientId}
 * Coach:       {$coachId}
 * Vigencia:    {$validFrom} → {$expiresAt}
 * Plan types:  {$planTypesList}
 *
 * Ejecutar en container EasyPanel:
 *   php /code/bootstrap/kb-prod/insert_bundle_*.php
 *
 * Para escribir realmente, editar este archivo:
 *   const DRY_RUN = true;  → const DRY_RUN = false;
 */

const DRY_RUN    = true;  // ⚠️ Cambiar a false para escribir real.
const CLIENT_ID  = {$clientId};
const COACH_ID   = {$coachId};
const VALID_FROM = '{$validFrom}';
const EXPIRES_AT = '{$expiresAt}';

\$now = date('Y-m-d H:i:s');

// ─── Bundle: $plansCount planes ─────────────────────────────────────────────
\$bundle = {$plansExport};

echo "═══ insert_bundle ($plansCount planes) ═══\\n";
echo "DRY_RUN:    " . (DRY_RUN ? 'true (no escribe)' : 'false (ESCRIBE)') . "\\n";
echo "client_id:  " . CLIENT_ID . "\\n";
echo "coach_id:   " . COACH_ID . "\\n";
echo "valid_from: " . VALID_FROM . "\\n";
echo "expires_at: " . EXPIRES_AT . "\\n";
echo "planes:\\n";
foreach (\$bundle as \$p) {
    echo "  · {\$p['plan_type']} (composed_id={\$p['composed_id']}, methodology={\$p['methodology_slug']}, " . strlen(json_encode(\$p['plan_json'])) . " bytes)\\n";
}
echo "\\n";

if (DRY_RUN) {
    echo "[DRY-RUN] Verificando sin escribir...\\n";
}

\$pdo = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness',
    'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// ─── 1. Verificar cliente ───────────────────────────────────────────────────
\$stmt = \$pdo->prepare("SELECT id, full_name, email FROM clients WHERE id = ? LIMIT 1");
\$stmt->execute([CLIENT_ID]);
\$client = \$stmt->fetch(PDO::FETCH_ASSOC);
if (! \$client) {
    fwrite(STDERR, "✗ ERROR: cliente CLIENT_ID=" . CLIENT_ID . " no existe.\\n");
    exit(1);
}
echo "✓ Cliente: #{\$client['id']} {\$client['full_name']} <{\$client['email']}>\\n";

// ─── 2. Verificar coach ─────────────────────────────────────────────────────
\$stmt = \$pdo->prepare("SELECT id, full_name FROM coaches WHERE id = ? LIMIT 1");
\$stmt->execute([COACH_ID]);
\$coach = \$stmt->fetch(PDO::FETCH_ASSOC);
if (! \$coach) {
    fwrite(STDERR, "✗ ERROR: coach COACH_ID=" . COACH_ID . " no existe.\\n");
    exit(1);
}
echo "✓ Coach: #{\$coach['id']} {\$coach['full_name']}\\n";

// ─── 3. Planes activos previos por plan_type ───────────────────────────────
\$planTypes = array_column(\$bundle, 'plan_type');
\$placeholders = implode(',', array_fill(0, count(\$planTypes), '?'));
\$stmt = \$pdo->prepare(
    "SELECT id, plan_type, valid_from, expires_at FROM assigned_plans
     WHERE client_id = ? AND plan_type IN (\$placeholders) AND active = 1
     ORDER BY plan_type, id DESC"
);
\$stmt->execute([CLIENT_ID, ...\$planTypes]);
\$prev = \$stmt->fetchAll(PDO::FETCH_ASSOC);
echo "→ Planes activos previos por plan_type:\\n";
foreach (\$prev as \$p) {
    echo "   #{\$p['id']} {\$p['plan_type']} ({\$p['valid_from']} → {\$p['expires_at']})\\n";
}

if (DRY_RUN) {
    echo "\\n[DRY-RUN] OK. Si todo se ve bien, editar este archivo y poner DRY_RUN=false.\\n";
    exit(0);
}

// ─── 4. WRITE: 1 sola transaction (desactivar todos + insertar todos) ──────
try {
    \$pdo->beginTransaction();

    // 4a. Desactivar planes previos activos del cliente para los plan_types del bundle
    \$stmtDeact = \$pdo->prepare(
        "UPDATE assigned_plans SET active = 0
         WHERE client_id = ? AND plan_type IN (\$placeholders) AND active = 1"
    );
    \$stmtDeact->execute([CLIENT_ID, ...\$planTypes]);
    \$desactivados = \$stmtDeact->rowCount();

    // 4b. Insertar todos los nuevos
    \$stmtIns = \$pdo->prepare(
        "INSERT INTO assigned_plans
         (client_id, plan_type, content, assigned_by, valid_from, expires_at, active, created_at)
         VALUES (?, ?, ?, ?, ?, ?, 1, ?)"
    );

    \$insertedIds = [];
    foreach (\$bundle as \$p) {
        \$stmtIns->execute([
            CLIENT_ID, \$p['plan_type'],
            json_encode(\$p['plan_json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            COACH_ID, VALID_FROM, EXPIRES_AT, \$now,
        ]);
        \$insertedIds[\$p['plan_type']] = \$pdo->lastInsertId();
    }

    \$pdo->commit();

    echo "\\n✓ OK — Bundle insertado en wellcore_fitness.assigned_plans\\n";
    echo "   · Planes previos desactivados: \$desactivados\\n";
    foreach (\$insertedIds as \$type => \$id) {
        echo "   · \$type → assigned_plan_id=\$id\\n";
    }
    echo "\\nSiguiente paso (invalidar cache del cliente):\\n";
    echo "   php artisan tinker --execute=\"\\\\Cache::forget('client_plan_v3_" . CLIENT_ID . "'); \\\\Cache::forget('wp:plan:" . CLIENT_ID . "'); \\\\Cache::forget('wp:weekdays:" . CLIENT_ID . "'); \\\\Cache::forget('dashboard:" . CLIENT_ID . "'); echo 'cache invalidated';\"\\n";
} catch (Exception \$e) {
    \$pdo->rollBack();
    fwrite(STDERR, "✗ ERROR (rollback aplicado): " . \$e->getMessage() . "\\n");
    fwrite(STDERR, \$e->getTraceAsString() . "\\n");
    exit(1);
}

PHP;
    }
}
