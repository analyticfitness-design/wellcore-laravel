<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * plan:export-prod-script — genera un archivo PHP standalone listo para ejecutar
 * en el container EasyPanel para insertar el plan en producción.
 *
 * Patrón seguro (mismo que bootstrap/insert_lizeth_plans.php):
 *   1. NO conecta a producción desde laptop — solo genera archivo.
 *   2. El archivo tiene DRY_RUN=true por default (audit primero, escribe después).
 *   3. Daniel sube el archivo a EasyPanel y lo ejecuta allá.
 *   4. Script idempotente: desactiva planes previos activos del mismo cliente+tipo,
 *      luego inserta el nuevo dentro de transaction.
 *   5. Incluye verificaciones (cliente existe, plan_type válido).
 *   6. Audit log: marca composed_plans.status='exported' con export_path.
 *
 * Uso:
 *   php artisan plan:export-prod-script --composed-id=12 \
 *       --client-id=98 --coach-id=7 --plan-type=entrenamiento
 *
 *   Output: bootstrap/kb-prod/insert_plan_kb_12_2026-05-17_143022.php
 */
final class PlanExportProdScriptCommand extends Command
{
    protected $signature = 'plan:export-prod-script
                            {--composed-id= : ID en wellcore_kb.composed_plans}
                            {--client-id= : client_id real en producción wellcore_fitness}
                            {--coach-id= : coach_id (assigned_by)}
                            {--plan-type= : override plan_type (default = plan_json.plan_type)}
                            {--valid-from= : YYYY-MM-DD (default = plan.fecha_inicio o mañana)}
                            {--expires-at= : YYYY-MM-DD (default = valid_from + duracion_semanas)}
                            {--out= : path output (default = bootstrap/kb-prod/insert_plan_kb_<id>_<timestamp>.php)}';

    protected $description = 'Genera script PHP standalone para subir un plan compuesto a producción wellcore_fitness.assigned_plans.';

    public function handle(): int
    {
        $composedId = $this->option('composed-id');
        $clientId = $this->option('client-id');
        $coachId = $this->option('coach-id');

        if (! $composedId || ! $clientId || ! $coachId) {
            $this->error('Requeridos: --composed-id, --client-id, --coach-id');
            return 2;
        }

        $composed = ComposedPlan::find($composedId);
        if (! $composed) {
            $this->error("composed_plans #$composedId no encontrado.");
            return 2;
        }

        $planJson = $composed->planJson();
        if ($planJson === []) {
            $this->error("composed_plans #$composedId tiene plan_json vacío o inválido.");
            return 2;
        }

        $planType = $this->option('plan-type') ?? ($planJson['plan_type'] ?? null);
        if (! in_array($planType, ['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo'], true)) {
            $this->error("plan_type inválido: '$planType' (debe ser entrenamiento|nutricion|suplementacion|habitos|ciclo).");
            return 2;
        }

        $validFrom = $this->option('valid-from') ?? ($planJson['fecha_inicio'] ?? now()->addDay()->toDateString());
        $duracionSemanas = (int) ($planJson['duracion_semanas'] ?? 4);
        $expiresAt = $this->option('expires-at') ?? \Carbon\Carbon::parse($validFrom)->addWeeks($duracionSemanas)->toDateString();

        $timestamp = now()->format('Y-m-d_His');
        $defaultOut = base_path("bootstrap/kb-prod/insert_plan_kb_{$composedId}_{$timestamp}.php");
        $outPath = $this->option('out') ?? $defaultOut;

        $outDir = dirname($outPath);
        if (! is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        $script = $this->buildScript(
            composedId: (int) $composedId,
            clientId: (int) $clientId,
            coachId: (int) $coachId,
            planType: $planType,
            validFrom: $validFrom,
            expiresAt: $expiresAt,
            planJson: $planJson,
            sourceMeta: [
                'methodology_slug' => $composed->methodology_slug,
                'client_handle' => $composed->client_handle,
                'violations_after' => $composed->violations_after,
                'composed_at' => $composed->created_at?->toIso8601String(),
            ],
        );

        file_put_contents($outPath, $script);

        $composed->update([
            'export_path' => $outPath,
            'notes' => trim(($composed->notes ?? '') . "\n[" . now()->toIso8601String() . "] export-prod-script generado para client_id=$clientId"),
        ]);

        // Path relativo al proyecto (para mostrar comando portable).
        $relPath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $outPath);
        $relPath = str_replace('\\', '/', $relPath);

        $this->info("✓ Script generado: $relPath");
        $this->newLine();
        $this->info('Pasos para subir a producción:');
        $this->line("1. Verificar el contenido: cat $relPath");
        $this->line('2. Commit + push del archivo al repo (git push origin main)');
        $this->line('3. En EasyPanel: ejecutar silvia-gitpull-load para que el container vea el archivo');
        $this->line('4. En consola del container (DRY-RUN primero):');
        $this->line("   php /code/$relPath  # DRY_RUN=true por default");
        $this->line('5. Si el dry-run reporta OK, editar el archivo y poner DRY_RUN=false');
        $this->line("6. Re-ejecutar: php /code/$relPath");
        $this->line('7. Invalidar cache del cliente (el script imprime el comando exacto al final)');

        return 0;
    }

    private function buildScript(
        int $composedId,
        int $clientId,
        int $coachId,
        string $planType,
        string $validFrom,
        string $expiresAt,
        array $planJson,
        array $sourceMeta,
    ): string {
        $planJsonEncoded = json_encode($planJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $planJsonExport = var_export($planJson, true);
        $sourceMetaExport = var_export($sourceMeta, true);
        $generatedAt = now()->toIso8601String();

        return <<<PHP
<?php

/**
 * insert_plan_kb_{$composedId}.php — generado automáticamente por plan:export-prod-script
 *
 * Inserta un plan generado por el motor v2 (wellcore_kb.composed_plans #{$composedId})
 * en la tabla wellcore_fitness.assigned_plans (producción).
 *
 * Generado:    {$generatedAt}
 * Cliente:     {$clientId}
 * Coach:       {$coachId}
 * Tipo:        {$planType}
 * Vigencia:    {$validFrom} → {$expiresAt}
 * Source meta: {$sourceMetaExport}
 *
 * Ejecutar en container EasyPanel:
 *   php /code/bootstrap/kb-prod/insert_plan_kb_{$composedId}_*.php
 *
 * Para escribir realmente, editar este archivo y cambiar:
 *   const DRY_RUN = true;  → const DRY_RUN = false;
 */

const DRY_RUN     = true;  // ⚠️ Cambiar a false para escribir real.
const COMPOSED_ID = {$composedId};
const CLIENT_ID   = {$clientId};
const COACH_ID    = {$coachId};
const PLAN_TYPE   = '{$planType}';
const VALID_FROM  = '{$validFrom}';
const EXPIRES_AT  = '{$expiresAt}';

\$now = date('Y-m-d H:i:s');

// ─── Plan JSON (snapshot generado por ComposeEngine) ────────────────────────
\$planArray = {$planJsonExport};

echo "═══ insert_plan_kb_" . COMPOSED_ID . " ═══\\n";
echo "DRY_RUN:    " . (DRY_RUN ? 'true (no escribe)' : 'false (ESCRIBE)') . "\\n";
echo "client_id:  " . CLIENT_ID . "\\n";
echo "coach_id:   " . COACH_ID . "\\n";
echo "plan_type:  " . PLAN_TYPE . "\\n";
echo "valid_from: " . VALID_FROM . "\\n";
echo "expires_at: " . EXPIRES_AT . "\\n";
echo "plan size:  " . strlen(json_encode(\$planArray)) . " bytes\\n";
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

// ─── 1. Verificar que el cliente existe ─────────────────────────────────────
\$stmt = \$pdo->prepare("SELECT id, full_name, email FROM clients WHERE id = ? LIMIT 1");
\$stmt->execute([CLIENT_ID]);
\$client = \$stmt->fetch(PDO::FETCH_ASSOC);
if (! \$client) {
    fwrite(STDERR, "✗ ERROR: cliente CLIENT_ID=" . CLIENT_ID . " no existe en wellcore_fitness.clients\\n");
    exit(1);
}
echo "✓ Cliente: #{\$client['id']} {\$client['full_name']} <{\$client['email']}>\\n";

// ─── 2. Verificar que el coach existe ───────────────────────────────────────
\$stmt = \$pdo->prepare("SELECT id, full_name FROM coaches WHERE id = ? LIMIT 1");
\$stmt->execute([COACH_ID]);
\$coach = \$stmt->fetch(PDO::FETCH_ASSOC);
if (! \$coach) {
    fwrite(STDERR, "✗ ERROR: coach COACH_ID=" . COACH_ID . " no existe en wellcore_fitness.coaches\\n");
    exit(1);
}
echo "✓ Coach: #{\$coach['id']} {\$coach['full_name']}\\n";

// ─── 3. Ver planes activos previos del cliente (mismo plan_type) ────────────
\$stmt = \$pdo->prepare(
    "SELECT id, valid_from, expires_at FROM assigned_plans
     WHERE client_id = ? AND plan_type = ? AND active = 1
     ORDER BY id DESC"
);
\$stmt->execute([CLIENT_ID, PLAN_TYPE]);
\$prev = \$stmt->fetchAll(PDO::FETCH_ASSOC);
echo "→ Planes activos previos (" . PLAN_TYPE . "): " . count(\$prev) . "\\n";
foreach (\$prev as \$p) {
    echo "   #{\$p['id']} {\$p['valid_from']} → {\$p['expires_at']}\\n";
}

if (DRY_RUN) {
    echo "\\n[DRY-RUN] OK. Si todo se ve bien, editar este archivo y poner DRY_RUN=false.\\n";
    exit(0);
}

// ─── 4. WRITE: transaction (desactivar previos + insertar nuevo) ────────────
try {
    \$pdo->beginTransaction();

    \$stmtDeact = \$pdo->prepare(
        "UPDATE assigned_plans SET active = 0
         WHERE client_id = ? AND plan_type = ? AND active = 1"
    );
    \$stmtDeact->execute([CLIENT_ID, PLAN_TYPE]);
    \$desactivados = \$stmtDeact->rowCount();

    \$stmtIns = \$pdo->prepare(
        "INSERT INTO assigned_plans
         (client_id, plan_type, content, assigned_by, valid_from, expires_at, active, created_at)
         VALUES (?, ?, ?, ?, ?, ?, 1, ?)"
    );
    \$stmtIns->execute([
        CLIENT_ID, PLAN_TYPE,
        json_encode(\$planArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        COACH_ID, VALID_FROM, EXPIRES_AT, \$now,
    ]);
    \$newId = \$pdo->lastInsertId();

    \$pdo->commit();

    echo "\\n✓ OK — Plan insertado en wellcore_fitness.assigned_plans\\n";
    echo "   · assigned_plan_id: \$newId\\n";
    echo "   · Planes previos desactivados: \$desactivados\\n";
    echo "\\nSiguiente paso (invalidar cache del cliente):\\n";
    echo "   php artisan tinker --execute=\"\\\\Cache::forget('client_plan_v3_" . CLIENT_ID . "'); \\\\Cache::forget('wp:plan:" . CLIENT_ID . "'); \\\\Cache::forget('wp:weekdays:" . CLIENT_ID . "'); \\\\Cache::forget('dashboard:" . CLIENT_ID . "'); echo 'cache invalidated';\"\\n";
} catch (Exception \$e) {
    \$pdo->rollBack();
    fwrite(STDERR, "✗ ERROR: " . \$e->getMessage() . "\\n");
    fwrite(STDERR, \$e->getTraceAsString() . "\\n");
    exit(1);
}

PHP;
    }
}
