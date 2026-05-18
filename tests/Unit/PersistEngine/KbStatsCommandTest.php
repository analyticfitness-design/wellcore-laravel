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

function makeComposedPlan(
    string $planType,
    string $methodologySlug,
    string $status,
    string $handle,
    ?string $exportPath = null,
    int $violationsBefore = 0,
    int $violationsAfter = 0,
    ?string $createdAt = null,
): void {
    $row = ComposedPlan::create([
        'client_handle' => $handle,
        'plan_type' => $planType,
        'methodology_slug' => $methodologySlug,
        'profile_json' => ['vertical' => $planType],
        'plan_json' => json_encode(['plan_type' => $planType]),
        'violations_before' => $violationsBefore,
        'violations_after' => $violationsAfter,
        'status' => $status,
        'export_path' => $exportPath,
        'compose_duration_ms' => 25.0,
        'lint_duration_ms' => 5.0,
    ]);
    if ($createdAt !== null) {
        $row->update(['created_at' => $createdAt, 'updated_at' => $createdAt]);
    }
}

function runKbStats(array $args = []): array
{
    Artisan::call('kb:stats', array_merge(['--json' => true], $args));
    $output = Artisan::output();
    $decoded = json_decode($output, true);
    return $decoded ?? [];
}

it('comando termina con 0 cuando no hay datos', function () {
    $exit = Artisan::call('kb:stats');
    expect($exit)->toBe(0);
});

it('counts plan_type y methodology correctamente', function () {
    makeComposedPlan('entrenamiento', 'body_part_split_5d', 'validated', 'A');
    makeComposedPlan('entrenamiento', 'body_part_split_5d', 'validated', 'B');
    makeComposedPlan('nutricion', 'iifym_deficit', 'validated', 'A');

    $stats = runKbStats();
    expect($stats['total'])->toBe(3);
    expect($stats['by_plan_type']['entrenamiento'])->toBe(2);
    expect($stats['by_plan_type']['nutricion'])->toBe(1);
    expect($stats['by_methodology']['body_part_split_5d'])->toBe(2);
    expect($stats['by_methodology']['iifym_deficit'])->toBe(1);
});

it('--vertical filtra a un solo plan_type', function () {
    makeComposedPlan('entrenamiento', 'body_part_split_5d', 'validated', 'A');
    makeComposedPlan('nutricion', 'iifym_deficit', 'validated', 'A');
    makeComposedPlan('habitos', 'habitos_sueno_hidratacion_basico', 'validated', 'A');

    $stats = runKbStats(['--vertical' => 'entrenamiento']);
    expect($stats['total'])->toBe(1);
    expect($stats['by_plan_type'])->toHaveKey('entrenamiento');
    expect($stats['by_plan_type'])->not->toHaveKey('nutricion');
});

it('top_handles ordenado por count DESC', function () {
    makeComposedPlan('entrenamiento', 'x', 'validated', 'cliente-A');
    makeComposedPlan('nutricion', 'x', 'validated', 'cliente-A');
    makeComposedPlan('suplementacion', 'x', 'validated', 'cliente-A');
    makeComposedPlan('entrenamiento', 'x', 'validated', 'cliente-B');

    $stats = runKbStats();
    expect($stats['top_handles'])->toHaveCount(2);
    expect($stats['top_handles'][0]['handle'])->toBe('cliente-A');
    expect($stats['top_handles'][0]['planes'])->toBe(3);
    expect($stats['top_handles'][1]['handle'])->toBe('cliente-B');
    expect($stats['top_handles'][1]['planes'])->toBe(1);
});

it('export_rate calcula porcentaje de exported correctamente', function () {
    makeComposedPlan('entrenamiento', 'x', 'exported', 'A', exportPath: '/tmp/a.php');
    makeComposedPlan('entrenamiento', 'x', 'exported', 'A', exportPath: '/tmp/b.php');
    makeComposedPlan('entrenamiento', 'x', 'validated', 'A'); // sin export

    $stats = runKbStats();
    expect($stats['export_rate']['exported_to_prod'])->toBe(2);
    expect($stats['export_rate']['percentage'])->toBe(66.7);
});

it('auto_fix_effectiveness calcula resolved + percentage', function () {
    makeComposedPlan('entrenamiento', 'x', 'validated', 'A', violationsBefore: 5, violationsAfter: 0);
    makeComposedPlan('nutricion', 'x', 'validated', 'A', violationsBefore: 3, violationsAfter: 1);

    $stats = runKbStats();
    expect($stats['violations']['total_before_fix'])->toBe(8);
    expect($stats['violations']['total_after_fix'])->toBe(1);
    expect($stats['auto_fix_effectiveness']['violations_resolved'])->toBe(7);
    expect($stats['auto_fix_effectiveness']['effectiveness_pct'])->toBe(87.5);
});

it('--since filtra por fecha', function () {
    makeComposedPlan('entrenamiento', 'x', 'validated', 'OLD', createdAt: '2025-01-01 00:00:00');
    makeComposedPlan('entrenamiento', 'x', 'validated', 'NEW');

    $stats = runKbStats(['--since' => '2026-01-01']);
    expect($stats['total'])->toBe(1);
    expect($stats['top_handles'][0]['handle'])->toBe('NEW');
});

it('plans_with_zero_violations cuenta correctamente', function () {
    makeComposedPlan('entrenamiento', 'x', 'validated', 'A', violationsBefore: 0, violationsAfter: 0);
    makeComposedPlan('entrenamiento', 'x', 'validated', 'B', violationsBefore: 2, violationsAfter: 2);
    makeComposedPlan('entrenamiento', 'x', 'validated', 'C', violationsBefore: 5, violationsAfter: 0);

    $stats = runKbStats();
    expect($stats['violations']['plans_with_zero_violations'])->toBe(2);
});

it('output performance promedio calcula correctamente', function () {
    makeComposedPlan('entrenamiento', 'x', 'validated', 'A');
    makeComposedPlan('nutricion', 'x', 'validated', 'A');

    $stats = runKbStats();
    expect((float) $stats['avg_compose_ms'])->toBe(25.0);
    expect((float) $stats['avg_lint_ms'])->toBe(5.0);
});
