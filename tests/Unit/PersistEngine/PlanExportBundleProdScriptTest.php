<?php

declare(strict_types=1);

use App\Models\Kb\ComposedPlan;

/**
 * Tests del comando plan:export-bundle-prod-script.
 *
 * Verifica:
 *   - Falla si faltan flags requeridos
 *   - Falla si composed_ids contiene IDs inexistentes
 *   - Genera archivo con DRY_RUN=true default
 *   - Sintaxis PHP válida
 *   - Incluye todos los planes en $bundle
 *   - Transaction begin/commit/rollback presente
 *   - Actualiza export_path en cada composed_plans del bundle
 */

beforeEach(function () {
    ComposedPlan::truncate();

    // Crea 3 composed_plans de prueba (diferentes verticales)
    $base = [
        'client_handle' => 'test-bundle-export',
        'methodology_slug' => 'test_method',
        'profile_json' => ['vertical' => 'test'],
        'violations_before' => 0,
        'violations_after' => 0,
        'status' => 'validated',
    ];

    $this->ids = [];
    foreach (['entrenamiento', 'nutricion', 'suplementacion'] as $v) {
        $this->ids[] = ComposedPlan::create([
            ...$base,
            'plan_type' => $v,
            'plan_json' => json_encode([
                'plan_type' => $v,
                'titulo' => "Test $v",
                'duracion_semanas' => 4,
                'fecha_inicio' => '2026-06-01',
            ]),
        ])->id;
    }
});

afterEach(function () {
    ComposedPlan::truncate();
    $files = glob(base_path('bootstrap/kb-prod/test_bundle_*.php'));
    foreach ($files as $f) {
        @unlink($f);
    }
});

it('falla si faltan flags requeridos', function () {
    $exit = $this->artisan('plan:export-bundle-prod-script')->run();
    expect($exit)->toBe(2);
});

it('falla si algún composed_id no existe', function () {
    $exit = $this->artisan('plan:export-bundle-prod-script', [
        '--composed-ids' => implode(',', $this->ids) . ',999999',
        '--client-id' => 98,
        '--coach-id' => 7,
    ])->run();
    expect($exit)->toBe(2);
});

it('genera archivo con DRY_RUN=true default', function () {
    $outPath = base_path('bootstrap/kb-prod/test_bundle_dry_run.php');

    $this->artisan('plan:export-bundle-prod-script', [
        '--composed-ids' => implode(',', $this->ids),
        '--client-id' => 98,
        '--coach-id' => 7,
        '--out' => $outPath,
    ])->assertSuccessful();

    expect($outPath)->toBeFile();
    $contents = file_get_contents($outPath);
    expect($contents)->toContain('const DRY_RUN    = true;');
    @unlink($outPath);
});

it('genera archivo con sintaxis PHP válida', function () {
    $outPath = base_path('bootstrap/kb-prod/test_bundle_syntax.php');

    $this->artisan('plan:export-bundle-prod-script', [
        '--composed-ids' => implode(',', $this->ids),
        '--client-id' => 98,
        '--coach-id' => 7,
        '--out' => $outPath,
    ])->assertSuccessful();

    $exitCode = 0;
    exec('php -l ' . escapeshellarg($outPath) . ' 2>&1', $output, $exitCode);
    expect($exitCode)->toBe(0);
    @unlink($outPath);
});

it('incluye todos los plan_types del bundle en el archivo', function () {
    $outPath = base_path('bootstrap/kb-prod/test_bundle_all_types.php');

    $this->artisan('plan:export-bundle-prod-script', [
        '--composed-ids' => implode(',', $this->ids),
        '--client-id' => 98,
        '--coach-id' => 7,
        '--out' => $outPath,
    ])->assertSuccessful();

    $contents = file_get_contents($outPath);
    expect($contents)->toContain("'entrenamiento'");
    expect($contents)->toContain("'nutricion'");
    expect($contents)->toContain("'suplementacion'");
    @unlink($outPath);
});

it('incluye transaction atómica (1 begin/commit/rollback para todos)', function () {
    $outPath = base_path('bootstrap/kb-prod/test_bundle_transaction.php');

    $this->artisan('plan:export-bundle-prod-script', [
        '--composed-ids' => implode(',', $this->ids),
        '--client-id' => 98,
        '--coach-id' => 7,
        '--out' => $outPath,
    ])->assertSuccessful();

    $contents = file_get_contents($outPath);
    // Solo 1 transaction (1 beginTransaction, 1 commit, 1 rollback)
    expect(substr_count($contents, 'beginTransaction'))->toBe(1);
    expect(substr_count($contents, '$pdo->commit'))->toBe(1);
    expect(substr_count($contents, '$pdo->rollBack'))->toBe(1);
    @unlink($outPath);
});

it('contiene loop INSERT para los N planes (no N statements estáticos)', function () {
    $outPath = base_path('bootstrap/kb-prod/test_bundle_loop.php');

    $this->artisan('plan:export-bundle-prod-script', [
        '--composed-ids' => implode(',', $this->ids),
        '--client-id' => 98,
        '--coach-id' => 7,
        '--out' => $outPath,
    ])->assertSuccessful();

    $contents = file_get_contents($outPath);
    // Un único prepared INSERT statement + foreach
    expect(substr_count($contents, 'INSERT INTO assigned_plans'))->toBe(1);
    expect($contents)->toContain('foreach ($bundle as $p)');
    @unlink($outPath);
});

it('actualiza export_path en todos los composed_plans del bundle', function () {
    $outPath = base_path('bootstrap/kb-prod/test_bundle_export_path.php');

    $this->artisan('plan:export-bundle-prod-script', [
        '--composed-ids' => implode(',', $this->ids),
        '--client-id' => 98,
        '--coach-id' => 7,
        '--out' => $outPath,
    ])->assertSuccessful();

    foreach ($this->ids as $id) {
        $cp = ComposedPlan::find($id);
        expect($cp->export_path)->toBe($outPath);
    }
    @unlink($outPath);
});
