<?php

declare(strict_types=1);

use App\Models\Kb\ComposedPlan;

/**
 * Tests del comando plan:export-prod-script.
 *
 * Verifican que el archivo generado:
 *   - Tiene DRY_RUN=true por default (safety)
 *   - Contiene los valores correctos (client_id, coach_id, plan_type, dates)
 *   - Tiene sintaxis PHP válida (php -l)
 *   - Incluye verificaciones de cliente/coach existente
 *   - Marca composed_plans.export_path después de generar
 */

beforeEach(function () {
    // Limpio cualquier file residual de tests anteriores
    $files = glob(base_path('bootstrap/kb-prod/test_*.php'));
    foreach ($files as $f) {
        @unlink($f);
    }

    // Creo un composed_plan de prueba
    $this->composedId = ComposedPlan::create([
        'client_handle' => 'test-client',
        'plan_type' => 'entrenamiento',
        'methodology_slug' => 'body_part_split_5d',
        'profile_json' => ['vertical' => 'entrenamiento', 'goal' => 'hipertrofia'],
        'plan_json' => json_encode([
            'plan_type' => 'entrenamiento',
            'titulo' => 'Test plan',
            'duracion_semanas' => 4,
            'fecha_inicio' => '2026-06-01',
            'semanas' => [],
        ]),
        'violations_before' => 0,
        'violations_after' => 0,
        'status' => 'validated',
        'compose_duration_ms' => 12.5,
    ])->id;
});

afterEach(function () {
    ComposedPlan::find($this->composedId)?->delete();
    $files = glob(base_path('bootstrap/kb-prod/insert_plan_kb_' . $this->composedId . '_*.php'));
    foreach ($files as $f) {
        @unlink($f);
    }
});

it('falla si faltan flags requeridos', function () {
    $exit = $this->artisan('plan:export-prod-script')->run();
    expect($exit)->toBe(2);
});

it('falla si composed_id no existe', function () {
    $exit = $this->artisan('plan:export-prod-script', [
        '--composed-id' => 999999,
        '--client-id' => 1,
        '--coach-id' => 1,
    ])->run();
    expect($exit)->toBe(2);
});

it('falla si plan_type es inválido', function () {
    $exit = $this->artisan('plan:export-prod-script', [
        '--composed-id' => $this->composedId,
        '--client-id' => 1,
        '--coach-id' => 1,
        '--plan-type' => 'invalido',
    ])->run();
    expect($exit)->toBe(2);
});

it('genera archivo con DRY_RUN=true por default', function () {
    $outPath = base_path('bootstrap/kb-prod/test_dry_run_default.php');
    $this->artisan('plan:export-prod-script', [
        '--composed-id' => $this->composedId,
        '--client-id' => 98,
        '--coach-id' => 7,
        '--out' => $outPath,
    ])->assertSuccessful();

    expect($outPath)->toBeFile();
    $contents = file_get_contents($outPath);
    expect($contents)->toContain('const DRY_RUN     = true;');
    @unlink($outPath);
});

it('genera archivo con sintaxis PHP válida', function () {
    $outPath = base_path('bootstrap/kb-prod/test_syntax.php');
    $this->artisan('plan:export-prod-script', [
        '--composed-id' => $this->composedId,
        '--client-id' => 98,
        '--coach-id' => 7,
        '--out' => $outPath,
    ])->assertSuccessful();

    $exitCode = 0;
    exec('php -l ' . escapeshellarg($outPath) . ' 2>&1', $output, $exitCode);
    expect($exitCode)->toBe(0);
    @unlink($outPath);
});

it('incluye client_id, coach_id, plan_type, fechas en el archivo', function () {
    $outPath = base_path('bootstrap/kb-prod/test_constants.php');
    $this->artisan('plan:export-prod-script', [
        '--composed-id' => $this->composedId,
        '--client-id' => 98,
        '--coach-id' => 7,
        '--valid-from' => '2026-07-01',
        '--expires-at' => '2026-07-29',
        '--out' => $outPath,
    ])->assertSuccessful();

    $contents = file_get_contents($outPath);
    expect($contents)->toContain("const CLIENT_ID   = 98;");
    expect($contents)->toContain("const COACH_ID    = 7;");
    expect($contents)->toContain("const PLAN_TYPE   = 'entrenamiento';");
    expect($contents)->toContain("const VALID_FROM  = '2026-07-01';");
    expect($contents)->toContain("const EXPIRES_AT  = '2026-07-29';");
    @unlink($outPath);
});

it('incluye verificación de cliente y coach existentes en el script', function () {
    $outPath = base_path('bootstrap/kb-prod/test_verifications.php');
    $this->artisan('plan:export-prod-script', [
        '--composed-id' => $this->composedId,
        '--client-id' => 98,
        '--coach-id' => 7,
        '--out' => $outPath,
    ])->assertSuccessful();

    $contents = file_get_contents($outPath);
    expect($contents)->toContain('SELECT id, full_name, email FROM clients WHERE id = ?');
    expect($contents)->toContain('SELECT id, full_name FROM coaches WHERE id = ?');
    expect($contents)->toContain('no existe en wellcore_fitness.clients');
    @unlink($outPath);
});

it('incluye transaction begin/commit + UPDATE active=0 + INSERT', function () {
    $outPath = base_path('bootstrap/kb-prod/test_transaction.php');
    $this->artisan('plan:export-prod-script', [
        '--composed-id' => $this->composedId,
        '--client-id' => 98,
        '--coach-id' => 7,
        '--out' => $outPath,
    ])->assertSuccessful();

    $contents = file_get_contents($outPath);
    expect($contents)->toContain('$pdo->beginTransaction();');
    expect($contents)->toContain('UPDATE assigned_plans SET active = 0');
    expect($contents)->toContain('INSERT INTO assigned_plans');
    expect($contents)->toContain('$pdo->commit();');
    expect($contents)->toContain('$pdo->rollBack();');
    @unlink($outPath);
});

it('actualiza composed_plans.export_path después de generar', function () {
    $outPath = base_path('bootstrap/kb-prod/test_export_path_update.php');
    $this->artisan('plan:export-prod-script', [
        '--composed-id' => $this->composedId,
        '--client-id' => 98,
        '--coach-id' => 7,
        '--out' => $outPath,
    ])->assertSuccessful();

    $composed = ComposedPlan::find($this->composedId);
    expect($composed->export_path)->toBe($outPath);
    @unlink($outPath);
});

it('default expires_at = valid_from + duracion_semanas (4 sem)', function () {
    $outPath = base_path('bootstrap/kb-prod/test_default_expires.php');
    $this->artisan('plan:export-prod-script', [
        '--composed-id' => $this->composedId,
        '--client-id' => 98,
        '--coach-id' => 7,
        '--valid-from' => '2026-06-01',
        '--out' => $outPath,
    ])->assertSuccessful();

    $contents = file_get_contents($outPath);
    // 4 semanas después de 2026-06-01 = 2026-06-29
    expect($contents)->toContain("const EXPIRES_AT  = '2026-06-29';");
    @unlink($outPath);
});
