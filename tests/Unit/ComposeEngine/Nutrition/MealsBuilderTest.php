<?php

declare(strict_types=1);

use App\Services\ComposeEngine\Nutrition\MealsBuilder;

beforeEach(function () {
    $this->builder = new MealsBuilder();
});

it('build 5 meals con shares que suman ~1.00', function () {
    $macros = [
        'objetivo_cal' => 2400,
        'macros' => ['proteina_g' => 200, 'carbohidratos_g' => 250, 'grasas_g' => 80],
    ];
    $slots = $this->builder->build($macros, 5);

    expect($slots)->toHaveCount(5);
    $totalShare = array_sum(array_map(fn ($s) => $s->kcalShare, $slots));
    expect($totalShare)->toBeBetween(0.99, 1.01);
});

it('reparto canónico: Desayuno 20%, Snack 10%, Almuerzo 30%, Pre-entreno 15%, Cena 25%', function () {
    $macros = [
        'objetivo_cal' => 2400,
        'macros' => ['proteina_g' => 200, 'carbohidratos_g' => 250, 'grasas_g' => 80],
    ];
    $slots = $this->builder->build($macros, 5);

    expect($slots[0]->name)->toBe('Desayuno');
    expect($slots[0]->kcalShare)->toBe(0.20);
    expect($slots[1]->name)->toBe('Snack AM');
    expect($slots[1]->kcalShare)->toBe(0.10);
    expect($slots[2]->name)->toBe('Almuerzo');
    expect($slots[2]->kcalShare)->toBe(0.30);
    expect($slots[3]->name)->toBe('Pre-entreno');
    expect($slots[3]->kcalShare)->toBe(0.15);
    expect($slots[4]->name)->toBe('Cena');
    expect($slots[4]->kcalShare)->toBe(0.25);
});

it('targetKcal de cada slot = objetivo_cal × share', function () {
    $macros = [
        'objetivo_cal' => 2000,
        'macros' => ['proteina_g' => 180, 'carbohidratos_g' => 200, 'grasas_g' => 60],
    ];
    $slots = $this->builder->build($macros, 5);

    expect($slots[0]->targetKcal)->toBe(400);   // 2000*0.20
    expect($slots[2]->targetKcal)->toBe(600);   // 2000*0.30
    expect($slots[4]->targetKcal)->toBe(500);   // 2000*0.25
});

it('targetProteinaG distribuida proporcionalmente', function () {
    $macros = [
        'objetivo_cal' => 2000,
        'macros' => ['proteina_g' => 180, 'carbohidratos_g' => 200, 'grasas_g' => 60],
    ];
    $slots = $this->builder->build($macros, 5);
    // Almuerzo 30% → 180*0.30 = 54g
    expect($slots[2]->targetProteinaG)->toBe(54);
});

it('lanza si mealsCount != 5 (Sprint 7 limitation)', function () {
    $macros = [
        'objetivo_cal' => 2000,
        'macros' => ['proteina_g' => 180, 'carbohidratos_g' => 200, 'grasas_g' => 60],
    ];
    expect(fn () => $this->builder->build($macros, 3))->toThrow(RuntimeException::class);
});
