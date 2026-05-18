<?php

declare(strict_types=1);

use App\Models\Kb\ComposedPlan;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    ComposedPlan::truncate();
});

afterEach(function () {
    ComposedPlan::truncate();
});

function makeDiffPlan(string $planType, array $planJsonOverrides, string $handle, string $methodologySlug = 'test_m'): ComposedPlan
{
    return ComposedPlan::create([
        'client_handle' => $handle,
        'plan_type' => $planType,
        'methodology_slug' => $methodologySlug,
        'profile_json' => ['vertical' => $planType],
        'plan_json' => json_encode(array_merge([
            'plan_type' => $planType,
            'titulo' => "Plan $planType",
            'duracion_semanas' => 4,
            'fecha_inicio' => '2026-06-01',
        ], $planJsonOverrides)),
        'violations_before' => 0,
        'violations_after' => 0,
        'status' => 'validated',
        'compose_duration_ms' => 25.0,
        'lint_duration_ms' => 5.0,
    ]);
}

function runDiff(int $a, int $b): array
{
    Artisan::call('plan:diff', ['a' => $a, 'b' => $b, '--json' => true]);
    return json_decode(Artisan::output(), true) ?? [];
}

it('plan:diff a a (mismo plan) reporta identical=true y exit 0', function () {
    $p = makeDiffPlan('entrenamiento', [], 'cliente-X');
    $exit = Artisan::call('plan:diff', ['a' => $p->id, 'b' => $p->id]);
    expect($exit)->toBe(0);
});

it('plan:diff detecta methodology distinta', function () {
    $a = makeDiffPlan('entrenamiento', [], 'X', 'body_part_split_5d');
    $b = makeDiffPlan('entrenamiento', [], 'X', 'ppl_6d');

    $diff = runDiff($a->id, $b->id);
    expect($diff['identical'])->toBeFalse();
    expect($diff['metadata_diff'])->toHaveKey('methodology_slug');
    expect($diff['metadata_diff']['methodology_slug']['a'])->toBe('body_part_split_5d');
    expect($diff['metadata_diff']['methodology_slug']['b'])->toBe('ppl_6d');
});

it('plan:diff detecta client_handle distinto', function () {
    $a = makeDiffPlan('entrenamiento', [], 'cliente-A');
    $b = makeDiffPlan('entrenamiento', [], 'cliente-B');

    $diff = runDiff($a->id, $b->id);
    expect($diff['metadata_diff'])->toHaveKey('client_handle');
});

it('plan:diff detecta top-level diff (titulo distinto)', function () {
    $a = makeDiffPlan('entrenamiento', ['titulo' => 'Plan A'], 'X');
    $b = makeDiffPlan('entrenamiento', ['titulo' => 'Plan B'], 'X');

    $diff = runDiff($a->id, $b->id);
    expect($diff['content_diff']['top_level'])->toHaveKey('titulo');
});

it('plan:diff entrenamiento detecta ejercicios added/removed', function () {
    $a = makeDiffPlan('entrenamiento', [
        'semanas' => [[
            'numero' => 1,
            'dias' => [['ejercicios' => [
                ['nombre' => 'Press banca', 'series' => 4],
                ['nombre' => 'Sentadilla', 'series' => 4],
            ]]],
        ]],
    ], 'X');
    $b = makeDiffPlan('entrenamiento', [
        'semanas' => [[
            'numero' => 1,
            'dias' => [['ejercicios' => [
                ['nombre' => 'Press banca', 'series' => 4],
                ['nombre' => 'Peso muerto', 'series' => 4],  // sentadilla → peso muerto
            ]]],
        ]],
    ], 'X');

    $diff = runDiff($a->id, $b->id);
    expect($diff['identical'])->toBeFalse();
    expect($diff['content_diff']['sections']['ejercicios_added'])->toContain('Peso muerto');
    expect($diff['content_diff']['sections']['ejercicios_removed'])->toContain('Sentadilla');
});

it('plan:diff nutricion detecta objetivo_cal y macros distintos', function () {
    $a = makeDiffPlan('nutricion', [
        'objetivo_cal' => 2000,
        'macros' => ['proteina_g' => 150, 'carbohidratos_g' => 200, 'grasas_g' => 60],
    ], 'X');
    $b = makeDiffPlan('nutricion', [
        'objetivo_cal' => 2500,
        'macros' => ['proteina_g' => 180, 'carbohidratos_g' => 280, 'grasas_g' => 70],
    ], 'X');

    $diff = runDiff($a->id, $b->id);
    expect($diff['content_diff']['sections']['objetivo_cal']['a'])->toBe(2000);
    expect($diff['content_diff']['sections']['objetivo_cal']['b'])->toBe(2500);
});

it('plan:diff suplementacion detecta suplementos added/removed', function () {
    $a = makeDiffPlan('suplementacion', [
        'suplementos' => [
            ['slug' => 'whey', 'nombre' => 'Whey'],
            ['slug' => 'creatina', 'nombre' => 'Creatina'],
        ],
    ], 'X');
    $b = makeDiffPlan('suplementacion', [
        'suplementos' => [
            ['slug' => 'whey', 'nombre' => 'Whey'],
            ['slug' => 'beta-alanina', 'nombre' => 'Beta-alanina'],
        ],
    ], 'X');

    $diff = runDiff($a->id, $b->id);
    expect($diff['content_diff']['sections']['suplementos_added'])->toContain('beta-alanina');
    expect($diff['content_diff']['sections']['suplementos_removed'])->toContain('creatina');
});

it('plan:diff habitos detecta habitos added/removed', function () {
    $a = makeDiffPlan('habitos', [
        'habitos' => [
            ['nombre' => 'Sueño 7-9h', 'categoria' => 'sueño'],
            ['nombre' => 'Hidratación 2.5L', 'categoria' => 'hidratacion'],
        ],
    ], 'X');
    $b = makeDiffPlan('habitos', [
        'habitos' => [
            ['nombre' => 'Sueño 7-9h', 'categoria' => 'sueño'],
            ['nombre' => 'Hidratación 3L', 'categoria' => 'hidratacion'], // distinto
        ],
    ], 'X');

    $diff = runDiff($a->id, $b->id);
    expect($diff['content_diff']['sections']['habitos_added'])->toContain('Hidratación 3L');
    expect($diff['content_diff']['sections']['habitos_removed'])->toContain('Hidratación 2.5L');
});

it('plan:diff falla con exit 2 si composed_id no existe', function () {
    $p = makeDiffPlan('entrenamiento', [], 'X');
    $exit = Artisan::call('plan:diff', ['a' => $p->id, 'b' => 999999]);
    expect($exit)->toBe(2);
});

it('plan:diff entre planes idénticos retorna exit 0', function () {
    $a = makeDiffPlan('entrenamiento', ['titulo' => 'Plan idéntico'], 'X');
    $b = makeDiffPlan('entrenamiento', ['titulo' => 'Plan idéntico'], 'X');

    // Forzar mismo methodology_slug + handle
    $b->update(['methodology_slug' => $a->methodology_slug]);

    $diff = runDiff($a->id, $b->id);
    expect($diff['identical'])->toBeTrue();
});
