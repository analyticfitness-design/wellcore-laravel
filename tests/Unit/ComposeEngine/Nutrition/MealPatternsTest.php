<?php

declare(strict_types=1);

use App\Services\ComposeEngine\Nutrition\MealPatterns;

it('Desayuno incluye fruta y grasa', function () {
    $p = MealPatterns::forSlot('Desayuno');
    expect($p['include_fruit'])->toBeTrue();
    expect($p['include_fat'])->toBeTrue();
    expect($p['include_vegetable'])->toBeFalse();
});

it('Pre-entreno NO incluye grasa (retrasa digestión)', function () {
    $p = MealPatterns::forSlot('Pre-entreno');
    expect($p['include_fat'])->toBeFalse();
    expect($p['include_vegetable'])->toBeFalse();
});

it('Pre-entreno usa carbos rápidos (fruta o granos)', function () {
    $p = MealPatterns::forSlot('Pre-entreno');
    expect($p['carb_categories'])->toContain('fruta');
});

it('Pre-entreno permite proteína magra (suplemento o animal)', function () {
    $p = MealPatterns::forSlot('Pre-entreno');
    expect($p['protein_categories'])->toContain('proteina_suplemento');
});

it('Almuerzo incluye vegetal de acompañamiento', function () {
    $p = MealPatterns::forSlot('Almuerzo');
    expect($p['include_vegetable'])->toBeTrue();
});

it('Cena incluye vegetal de acompañamiento', function () {
    $p = MealPatterns::forSlot('Cena');
    expect($p['include_vegetable'])->toBeTrue();
});

it('Snack AM es liviano (lácteo o suplemento)', function () {
    $p = MealPatterns::forSlot('Snack AM');
    expect($p['protein_categories'])->toContain('proteina_lactea');
    expect($p['protein_categories'])->toContain('proteina_suplemento');
});

it('Snack AM usa fruta como carbo', function () {
    $p = MealPatterns::forSlot('Snack AM');
    expect($p['carb_categories'])->toContain('fruta');
});

it('Slot desconocido devuelve default razonable (no rompe)', function () {
    $p = MealPatterns::forSlot('CualquierCosa');
    expect($p)->toBeArray();
    expect($p['protein_categories'])->not->toBeEmpty();
});
