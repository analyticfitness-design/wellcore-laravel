<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

/**
 * Tests del comando plan:assert-deterministic.
 *
 * Verifica que el motor genera output idéntico para mismo profile + mismo seed.
 * Este es el test de regresión MÁS IMPORTANTE — si el motor pierde determinismo
 * por un refactor, lo detectamos aquí.
 */

it('motor v2 es determinístico en todas las verticales (F elite hipertrofia)', function () {
    $exit = Artisan::call('plan:assert-deterministic', [
        '--goal' => 'hipertrofia',
        '--level' => 'intermedio',
        '--days' => 5,
        '--gender' => 'F',
        '--tier' => 'elite',
    ]);
    expect($exit)->toBe(0);
});

it('motor v2 es determinístico para hombre esencial avanzado 6d', function () {
    $exit = Artisan::call('plan:assert-deterministic', [
        '--goal' => 'hipertrofia',
        '--level' => 'avanzado',
        '--days' => 6,
        '--gender' => 'M',
        '--tier' => 'esencial',
    ]);
    expect($exit)->toBe(0);
});

it('motor v2 es determinístico para perdida_grasa F elite 5d', function () {
    $exit = Artisan::call('plan:assert-deterministic', [
        '--goal' => 'perdida_grasa',
        '--level' => 'intermedio',
        '--days' => 5,
        '--gender' => 'F',
        '--tier' => 'elite',
    ]);
    expect($exit)->toBe(0);
});

it('--only filtra a una sola vertical', function () {
    $exit = Artisan::call('plan:assert-deterministic', [
        '--only' => 'entrenamiento',
        '--goal' => 'hipertrofia',
        '--level' => 'intermedio',
        '--days' => 5,
        '--gender' => 'F',
        '--tier' => 'elite',
    ]);
    expect($exit)->toBe(0);
});

it('verifica las 5 verticales en una sola corrida', function () {
    Artisan::call('plan:assert-deterministic', [
        '--goal' => 'hipertrofia',
        '--level' => 'intermedio',
        '--days' => 5,
        '--gender' => 'F',
        '--tier' => 'elite',
    ]);
    $output = Artisan::output();
    foreach (['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo'] as $v) {
        expect($output)->toContain($v);
    }
});
