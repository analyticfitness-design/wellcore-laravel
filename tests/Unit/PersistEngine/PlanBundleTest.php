<?php

declare(strict_types=1);

use App\Models\Kb\ComposedPlan;

/**
 * Tests del comando plan:bundle.
 *
 * Verifica:
 *   - Auto-detect de verticales aplicables (4 default, +ciclo si F elite)
 *   - --only filtra a subset
 *   - --skip excluye verticales
 *   - --json output estructurado
 *   - Cada vertical produce un composed_plans row
 *   - Status de validación correcto
 */

beforeEach(function () {
    ComposedPlan::truncate();
});

afterEach(function () {
    ComposedPlan::truncate();
});

it('M esencial procesa 4 verticales (sin ciclo)', function () {
    $exit = $this->artisan('plan:bundle', [
        '--goal' => 'hipertrofia',
        '--level' => 'avanzado',
        '--days' => 6,
        '--gender' => 'M',
        '--tier' => 'esencial',
        '--client-handle' => 'test-m-esencial',
        '--json' => true,
    ])->run();

    expect($exit)->toBe(0);
    $rows = ComposedPlan::all();
    expect($rows)->toHaveCount(4);
    $verticals = $rows->pluck('plan_type')->sort()->values()->toArray();
    expect($verticals)->toBe(['entrenamiento', 'habitos', 'nutricion', 'suplementacion']);
});

it('F elite procesa 5 verticales (incluye ciclo)', function () {
    $exit = $this->artisan('plan:bundle', [
        '--goal' => 'hipertrofia',
        '--level' => 'intermedio',
        '--days' => 5,
        '--gender' => 'F',
        '--tier' => 'elite',
        '--client-handle' => 'test-f-elite',
        '--json' => true,
    ])->run();

    expect($exit)->toBe(0);
    expect(ComposedPlan::count())->toBe(5);
    $verticals = ComposedPlan::pluck('plan_type')->sort()->values()->toArray();
    expect($verticals)->toBe(['ciclo', 'entrenamiento', 'habitos', 'nutricion', 'suplementacion']);
});

it('F esencial NO incluye ciclo (requiere tier elite/rise)', function () {
    $this->artisan('plan:bundle', [
        '--goal' => 'perdida_grasa',
        '--level' => 'intermedio',
        '--days' => 5,
        '--gender' => 'F',
        '--tier' => 'esencial',
        '--client-handle' => 'test-f-esencial',
    ])->run();

    expect(ComposedPlan::count())->toBe(4);
    expect(ComposedPlan::where('plan_type', 'ciclo')->exists())->toBeFalse();
});

it('M elite NO incluye ciclo (requiere gender F)', function () {
    $this->artisan('plan:bundle', [
        '--goal' => 'hipertrofia',
        '--level' => 'avanzado',
        '--days' => 6,
        '--gender' => 'M',
        '--tier' => 'elite',
        '--client-handle' => 'test-m-elite',
    ])->run();

    expect(ComposedPlan::count())->toBe(4);
    expect(ComposedPlan::where('plan_type', 'ciclo')->exists())->toBeFalse();
});

it('--only=entrenamiento,nutricion limita a 2 verticales', function () {
    $this->artisan('plan:bundle', [
        '--goal' => 'hipertrofia',
        '--level' => 'intermedio',
        '--days' => 5,
        '--gender' => 'F',
        '--tier' => 'elite',
        '--only' => 'entrenamiento,nutricion',
        '--client-handle' => 'test-only',
    ])->run();

    expect(ComposedPlan::count())->toBe(2);
    $verticals = ComposedPlan::pluck('plan_type')->sort()->values()->toArray();
    expect($verticals)->toBe(['entrenamiento', 'nutricion']);
});

it('--skip=habitos,suplementacion excluye esas verticales', function () {
    $this->artisan('plan:bundle', [
        '--goal' => 'hipertrofia',
        '--level' => 'avanzado',
        '--days' => 6,
        '--gender' => 'M',
        '--tier' => 'esencial',
        '--skip' => 'habitos,suplementacion',
        '--client-handle' => 'test-skip',
    ])->run();

    expect(ComposedPlan::count())->toBe(2);
    $verticals = ComposedPlan::pluck('plan_type')->sort()->values()->toArray();
    expect($verticals)->toBe(['entrenamiento', 'nutricion']);
});

it('todos los planes terminan status=validated cuando catálogo está sano', function () {
    $this->artisan('plan:bundle', [
        '--goal' => 'hipertrofia',
        '--level' => 'intermedio',
        '--days' => 5,
        '--gender' => 'F',
        '--tier' => 'elite',
        '--client-handle' => 'test-validated',
    ])->run();

    $rows = ComposedPlan::all();
    foreach ($rows as $r) {
        expect($r->status)->toBeIn(['validated', 'exported']);
    }
});

it('client_handle se propaga a todos los composed_plans del bundle', function () {
    $this->artisan('plan:bundle', [
        '--goal' => 'hipertrofia',
        '--level' => 'intermedio',
        '--days' => 5,
        '--gender' => 'F',
        '--tier' => 'elite',
        '--client-handle' => 'test-handle-propagation',
    ])->run();

    $handles = ComposedPlan::pluck('client_handle')->unique()->values()->toArray();
    expect($handles)->toBe(['test-handle-propagation']);
});

it('--export-dir crea archivos JSON por vertical', function () {
    $dir = base_path('storage/app/test-bundle-export-' . uniqid());

    $this->artisan('plan:bundle', [
        '--goal' => 'hipertrofia',
        '--level' => 'avanzado',
        '--days' => 6,
        '--gender' => 'M',
        '--tier' => 'esencial',
        '--export-dir' => $dir,
        '--client-handle' => 'test-export-dir',
    ])->run();

    foreach (['entrenamiento', 'nutricion', 'suplementacion', 'habitos'] as $v) {
        expect("$dir/plan_$v.json")->toBeFile();
        @unlink("$dir/plan_$v.json");
    }
    @rmdir($dir);
});
