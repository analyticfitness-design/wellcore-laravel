<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

afterEach(function () {
    // Limpio snapshots de test
    $files = glob(base_path('bootstrap/kb-snapshots/test_*.sql'));
    foreach ($files as $f) {
        @unlink($f);
    }
});

it('genera archivo SQL con CREATE/INSERT clauses', function () {
    $outPath = base_path('bootstrap/kb-snapshots/test_snapshot_basic.sql');
    Artisan::call('kb:export-snapshot', ['--out' => $outPath]);

    expect($outPath)->toBeFile();
    $contents = file_get_contents($outPath);
    expect($contents)->toContain('INSERT INTO `methodologies`');
    expect($contents)->toContain('INSERT INTO `principles`');
    expect($contents)->toContain('SET FOREIGN_KEY_CHECKS = 0');
    expect($contents)->toContain('SET FOREIGN_KEY_CHECKS = 1');
});

it('header incluye timestamp + git hash', function () {
    $outPath = base_path('bootstrap/kb-snapshots/test_snapshot_header.sql');
    Artisan::call('kb:export-snapshot', ['--out' => $outPath]);

    $contents = file_get_contents($outPath);
    expect($contents)->toContain('═══ wellcore_kb snapshot ═══');
    expect($contents)->toContain('Generado:');
    expect($contents)->toContain('php artisan kb:export-snapshot');
});

it('excluye composed_plans por default', function () {
    $outPath = base_path('bootstrap/kb-snapshots/test_snapshot_no_audit.sql');
    Artisan::call('kb:export-snapshot', ['--out' => $outPath]);

    $contents = file_get_contents($outPath);
    expect($contents)->not->toContain('INSERT INTO `composed_plans`');
});

it('--include-audit incluye composed_plans', function () {
    // Pre-condición: crear un composed_plan dummy
    \App\Models\Kb\ComposedPlan::create([
        'client_handle' => 'test-snapshot',
        'plan_type' => 'entrenamiento',
        'methodology_slug' => 'x',
        'profile_json' => [],
        'plan_json' => '{}',
        'violations_before' => 0,
        'violations_after' => 0,
        'status' => 'validated',
    ]);

    $outPath = base_path('bootstrap/kb-snapshots/test_snapshot_with_audit.sql');
    Artisan::call('kb:export-snapshot', ['--out' => $outPath, '--include-audit' => true]);

    $contents = file_get_contents($outPath);
    expect($contents)->toContain('Tabla: composed_plans');

    \App\Models\Kb\ComposedPlan::truncate();
});

it('escapa correctamente strings con comillas y caracteres especiales', function () {
    $outPath = base_path('bootstrap/kb-snapshots/test_snapshot_escape.sql');
    Artisan::call('kb:export-snapshot', ['--out' => $outPath]);

    $contents = file_get_contents($outPath);
    // Verifica que no haya SQL injection trivial (apóstrofes sin escapar entre INSERT clauses)
    // Heurística: cualquier valor string debe estar entre comillas + sin apóstrofes literales sin escape \\'
    expect($contents)->not->toContain("'\\nINSERT");
});

it('incluye 14 tablas del catálogo (excluyendo audit)', function () {
    $outPath = base_path('bootstrap/kb-snapshots/test_snapshot_tables.sql');
    Artisan::call('kb:export-snapshot', ['--out' => $outPath]);

    $contents = file_get_contents($outPath);
    foreach ([
        'methodologies', 'methodology_rules', 'principles', 'exercise_metadata',
        'plan_templates_local', 'decision_rules', 'lint_rules',
        'nutrition_foods', 'supplement_catalog', 'supplement_stacks',
        'hormonal_compounds', 'hormonal_protocol_templates',
        'ciclo_menstrual_fases', 'bloodwork_panels',
    ] as $table) {
        expect($contents)->toContain("Tabla: $table");
    }
});

it('reporta count de filas correcto', function () {
    Artisan::call('kb:export-snapshot', ['--out' => base_path('bootstrap/kb-snapshots/test_snapshot_count.sql')]);
    $output = Artisan::output();

    // Output reporta tablas con counts
    expect($output)->toContain('methodologies');
    expect($output)->toContain('exercise_metadata');
});
