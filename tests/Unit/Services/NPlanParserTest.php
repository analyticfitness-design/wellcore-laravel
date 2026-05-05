<?php

use App\Services\NutritionPlanParser;

describe('NutritionPlanParser', function () {
    test('extracts meals from root comidas array', function () {
        $plan = ['comidas' => [
            ['nombre' => 'Desayuno', 'calorias' => 400, 'alimentos' => ['Avena']],
            ['nombre' => 'Almuerzo', 'calorias' => 600, 'alimentos' => ['Pollo']],
        ]];
        $meals = NutritionPlanParser::extractMeals($plan);
        expect($meals)->toHaveCount(2);
        expect($meals[0]['nombre'])->toBe('Desayuno');
        expect($meals[0]['calorias'])->toBe(400);
    });

    test('extracts meals from dias array', function () {
        $plan = ['dias' => [['nombre' => 'Lunes', 'comidas' => [['nombre' => 'Desayuno', 'calorias' => 350]]]]];
        $meals = NutritionPlanParser::extractMeals($plan);
        expect($meals)->toHaveCount(1);
        expect($meals[0]['nombre'])->toBe('Desayuno');
    });

    test('extracts meals from plan_semanal array', function () {
        $plan = ['plan_semanal' => [['dia' => 'Lunes', 'comidas' => [['name' => 'Breakfast', 'calories' => 300]]]]];
        $meals = NutritionPlanParser::extractMeals($plan);
        expect($meals)->toHaveCount(1);
        expect($meals[0]['nombre'])->toBe('Breakfast');
        expect($meals[0]['calorias'])->toBe(300);
    });

    test('extracts meals from plan_dia_entrenamiento', function () {
        $plan = ['plan_dia_entrenamiento' => ['comidas' => [['nombre' => 'Pre-entreno', 'calorias' => 250]]]];
        $meals = NutritionPlanParser::extractMeals($plan);
        expect($meals)->toHaveCount(1);
        expect($meals[0]['nombre'])->toBe('Pre-entreno');
    });

    test('extracts meals from meals english key', function () {
        $plan = ['meals' => [['label' => 'Lunch', 'kcal' => 700, 'foods' => ['Rice', 'Chicken']]]];
        $meals = NutritionPlanParser::extractMeals($plan);
        expect($meals)->toHaveCount(1);
        expect($meals[0]['nombre'])->toBe('Lunch');
        expect($meals[0]['calorias'])->toBe(700);
        expect($meals[0]['alimentos'])->toBe(['Rice', 'Chicken']);
    });

    test('returns empty for unrecognized format', function () {
        expect(NutritionPlanParser::extractMeals([]))->toBe([]);
        expect(NutritionPlanParser::extractMeals(['random' => 'data']))->toBe([]);
    });

    test('normalizes macros with multiple key conventions', function () {
        $plan = ['comidas' => [['nombre' => 'X', 'macros' => ['proteina_g' => 30, 'carbs_g' => 50, 'grasa_g' => 10]]]];
        $meals = NutritionPlanParser::extractMeals($plan);
        expect($meals[0]['macros']['proteina'])->toBe(30);
        expect($meals[0]['macros']['carbohidratos'])->toBe(50);
        expect($meals[0]['macros']['grasas'])->toBe(10);
    });

    test('preserves meal order for meal_index', function () {
        $plan = ['comidas' => [['nombre' => 'Desayuno'], ['nombre' => 'Almuerzo'], ['nombre' => 'Cena']]];
        $meals = NutritionPlanParser::extractMeals($plan);
        expect($meals[0]['nombre'])->toBe('Desayuno');
        expect($meals[1]['nombre'])->toBe('Almuerzo');
        expect($meals[2]['nombre'])->toBe('Cena');
    });
});
