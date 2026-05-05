<?php

use App\Services\NutritionPlanParser;

describe('NutritionPlanParser regression — real plan formats', function () {
    test('jairo plan: 3 root comidas', function () {
        $plan = ['comidas' => [
            ['nombre' => 'Desayuno 7am', 'calorias' => 450, 'alimentos' => ['Huevos', 'Avena']],
            ['nombre' => 'Almuerzo 1pm', 'calorias' => 700],
            ['nombre' => 'Cena 7pm', 'calorias' => 550],
        ]];
        $meals = NutritionPlanParser::extractMeals($plan);
        expect($meals)->toHaveCount(3);
        expect($meals[0]['nombre'])->toBe('Desayuno 7am');
    });

    test('carb cycling dias array', function () {
        $plan = ['dias' => [
            ['dia' => 1, 'comidas' => [['nombre' => 'Comida alta carb 1']]],
            ['dia' => 2, 'comidas' => [['nombre' => 'Comida baja carb 1']]],
        ]];
        $meals = NutritionPlanParser::extractMeals($plan);
        expect($meals)->toHaveCount(1);
        expect($meals[0]['nombre'])->toBe('Comida alta carb 1');
    });

    test('plan_dia_entrenamiento root', function () {
        $plan = ['plan_dia_entrenamiento' => ['comidas' => [['nombre' => 'Pre-entreno']]]];
        $meals = NutritionPlanParser::extractMeals($plan);
        expect($meals)->toHaveCount(1);
        expect($meals[0]['nombre'])->toBe('Pre-entreno');
    });

    test('tatis english meals', function () {
        $plan = ['meals' => [['label' => 'Breakfast', 'kcal' => 380, 'foods' => ['oats']]]];
        $meals = NutritionPlanParser::extractMeals($plan);
        expect($meals)->toHaveCount(1);
        expect($meals[0]['nombre'])->toBe('Breakfast');
    });
});
