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

it('soporta 3 comidas con reparto 30/40/30', function () {
    $macros = [
        'objetivo_cal' => 2000,
        'macros' => ['proteina_g' => 180, 'carbohidratos_g' => 200, 'grasas_g' => 60],
    ];
    $slots = $this->builder->build($macros, 3);
    expect($slots)->toHaveCount(3);
    expect($slots[0]->name)->toBe('Desayuno');
    expect($slots[0]->kcalShare)->toBe(0.30);
    expect($slots[1]->name)->toBe('Almuerzo');
    expect($slots[1]->kcalShare)->toBe(0.40);
    expect($slots[2]->name)->toBe('Cena');
    expect($slots[2]->kcalShare)->toBe(0.30);
    $totalShare = array_sum(array_map(fn ($s) => $s->kcalShare, $slots));
    expect($totalShare)->toBeBetween(0.99, 1.01);
});

it('soporta 4 comidas con shares que suman ~1.00', function () {
    $macros = [
        'objetivo_cal' => 2000,
        'macros' => ['proteina_g' => 180, 'carbohidratos_g' => 200, 'grasas_g' => 60],
    ];
    $slots = $this->builder->build($macros, 4);
    expect($slots)->toHaveCount(4);
    $totalShare = array_sum(array_map(fn ($s) => $s->kcalShare, $slots));
    expect($totalShare)->toBeBetween(0.99, 1.01);
});

it('soporta 6 comidas con shares que suman ~1.00', function () {
    $macros = [
        'objetivo_cal' => 2000,
        'macros' => ['proteina_g' => 180, 'carbohidratos_g' => 200, 'grasas_g' => 60],
    ];
    $slots = $this->builder->build($macros, 6);
    expect($slots)->toHaveCount(6);
    $totalShare = array_sum(array_map(fn ($s) => $s->kcalShare, $slots));
    expect($totalShare)->toBeBetween(0.99, 1.01);
});

it('lanza si mealsCount fuera de [3,4,5,6]', function () {
    $macros = [
        'objetivo_cal' => 2000,
        'macros' => ['proteina_g' => 180, 'carbohidratos_g' => 200, 'grasas_g' => 60],
    ];
    expect(fn () => $this->builder->build($macros, 2))->toThrow(RuntimeException::class);
    expect(fn () => $this->builder->build($macros, 7))->toThrow(RuntimeException::class);
});

it('override de horarios via customTimes', function () {
    $macros = [
        'objetivo_cal' => 2000,
        'macros' => ['proteina_g' => 180, 'carbohidratos_g' => 200, 'grasas_g' => 60],
    ];
    // Karen Vanessa: 4 comidas a 5am/10am/1pm/4pm
    $slots = $this->builder->build($macros, 4, ['05:00', '10:00', '13:00', '16:00']);
    expect($slots[0]->horaSugerida)->toBe('05:00');
    expect($slots[1]->horaSugerida)->toBe('10:00');
    expect($slots[2]->horaSugerida)->toBe('13:00');
    expect($slots[3]->horaSugerida)->toBe('16:00');
});

it('ignora customTimes si count no matchea', function () {
    $macros = [
        'objetivo_cal' => 2000,
        'macros' => ['proteina_g' => 180, 'carbohidratos_g' => 200, 'grasas_g' => 60],
    ];
    // 4 comidas pero solo 3 horarios → ignora customTimes, usa canónicos
    $slots = $this->builder->build($macros, 4, ['05:00', '10:00', '13:00']);
    expect($slots[0]->horaSugerida)->toBe('07:00');  // canónico
});
