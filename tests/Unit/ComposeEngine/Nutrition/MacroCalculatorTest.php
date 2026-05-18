<?php

declare(strict_types=1);

use App\Services\ComposeEngine\Nutrition\MacroCalculator;
use App\Services\DecisionEngine\Data\ClientProfile;

beforeEach(function () {
    $this->calc = new MacroCalculator();
});

it('calcula BMR Mifflin-St Jeor para hombre', function () {
    // Hombre 80kg, 175cm, 30 años → BMR = 10*80 + 6.25*175 - 5*30 + 5 = 1748.75
    $profile = new ClientProfile(
        vertical: 'nutricion', goal: 'mantenimiento', level: 'intermedio',
        gender: 'M', age: 30, weightKg: 80, heightCm: 175,
    );
    $result = $this->calc->calculate($profile);
    expect($result['bmr'])->toBeBetween(1740, 1755);
});

it('calcula BMR Mifflin-St Jeor para mujer (resta 161 vs hombre +5)', function () {
    // Mujer 65kg, 165cm, 28 años → BMR = 10*65 + 6.25*165 - 5*28 - 161 = 1380.25
    $profile = new ClientProfile(
        vertical: 'nutricion', goal: 'mantenimiento', level: 'intermedio',
        gender: 'F', age: 28, weightKg: 65, heightCm: 165,
    );
    $result = $this->calc->calculate($profile);
    expect($result['bmr'])->toBeBetween(1375, 1385);
});

it('TDEE multiplica BMR por activity factor según level', function () {
    $profile = new ClientProfile(
        vertical: 'nutricion', goal: 'mantenimiento', level: 'avanzado',
        gender: 'M', age: 30, weightKg: 80, heightCm: 175,
    );
    $result = $this->calc->calculate($profile);
    // BMR ~1749 × 1.725 = ~3017
    expect($result['tdee'])->toBeBetween(3000, 3030);
});

it('objetivo_cal con perdida_grasa resta 400 al TDEE', function () {
    $profile = new ClientProfile(
        vertical: 'nutricion', goal: 'perdida_grasa', level: 'intermedio',
        gender: 'M', age: 30, weightKg: 80, heightCm: 175,
    );
    $result = $this->calc->calculate($profile);
    expect($result['objetivo_cal'])->toBe($result['tdee'] - 400);
});

it('objetivo_cal con hipertrofia suma 250 al TDEE', function () {
    $profile = new ClientProfile(
        vertical: 'nutricion', goal: 'hipertrofia', level: 'intermedio',
        gender: 'M', age: 30, weightKg: 80, heightCm: 175,
    );
    $result = $this->calc->calculate($profile);
    expect($result['objetivo_cal'])->toBe($result['tdee'] + 250);
});

it('proteina 2.4g/kg en perdida_grasa (alta para preservar masa)', function () {
    $profile = new ClientProfile(
        vertical: 'nutricion', goal: 'perdida_grasa', level: 'intermedio',
        gender: 'M', age: 30, weightKg: 80, heightCm: 175,
    );
    $result = $this->calc->calculate($profile);
    // 80kg × 2.4 = 192g
    expect($result['macros']['proteina_g'])->toBe(192);
});

it('grasas 0.9g/kg consistente', function () {
    $profile = new ClientProfile(
        vertical: 'nutricion', goal: 'mantenimiento', level: 'intermedio',
        gender: 'M', age: 30, weightKg: 80, heightCm: 175,
    );
    $result = $this->calc->calculate($profile);
    expect($result['macros']['grasas_g'])->toBe(72); // 80*0.9
});

it('floor de seguridad: objetivo_cal nunca menor a 1200', function () {
    // Mujer 50kg con perdida_grasa agresiva → TDEE bajo → asegurar floor
    $profile = new ClientProfile(
        vertical: 'nutricion', goal: 'perdida_grasa', level: 'principiante',
        gender: 'F', age: 50, weightKg: 45, heightCm: 155,
    );
    $result = $this->calc->calculate($profile);
    expect($result['objetivo_cal'])->toBeGreaterThanOrEqual(1200);
});

it('defaults conservadores cuando faltan datos del profile', function () {
    $profile = new ClientProfile(vertical: 'nutricion', goal: 'mantenimiento');
    $result = $this->calc->calculate($profile);
    expect($result['meta']['weight_used_kg'])->toBe(70.0);
    expect($result['meta']['height_used_cm'])->toBe(170.0);
    expect($result['meta']['age_used'])->toBe(30);
    expect($result['meta']['gender_used'])->toBe('M');
});

it('normaliza gender F/femenino/female/mujer → F', function () {
    foreach (['F', 'femenino', 'female', 'mujer'] as $g) {
        $profile = new ClientProfile(vertical: 'nutricion', goal: 'mantenimiento', gender: $g);
        $result = $this->calc->calculate($profile);
        expect($result['meta']['gender_used'])->toBe('F');
    }
});

it('cualquier otro gender → M (default)', function () {
    $profile = new ClientProfile(vertical: 'nutricion', goal: 'mantenimiento', gender: 'hombre');
    $result = $this->calc->calculate($profile);
    expect($result['meta']['gender_used'])->toBe('M');
});

it('macros suman aproximadamente a objetivo_cal (±20 kcal)', function () {
    $profile = new ClientProfile(
        vertical: 'nutricion', goal: 'perdida_grasa', level: 'intermedio',
        gender: 'M', age: 30, weightKg: 80, heightCm: 175,
    );
    $result = $this->calc->calculate($profile);
    $totalKcal = $result['macros']['proteina_g'] * 4
        + $result['macros']['carbohidratos_g'] * 4
        + $result['macros']['grasas_g'] * 9;

    expect(abs($totalKcal - $result['objetivo_cal']))->toBeLessThan(20);
});
