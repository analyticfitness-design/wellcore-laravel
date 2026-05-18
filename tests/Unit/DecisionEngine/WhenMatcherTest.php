<?php

declare(strict_types=1);

use App\Services\DecisionEngine\Data\ClientProfile;
use App\Services\DecisionEngine\WhenMatcher;

beforeEach(function () {
    $this->matcher = new WhenMatcher();
});

it('matchea cuando todas las keys del when están presentes y son iguales en el profile', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', goal: 'hipertrofia', level: 'intermedio', days: 5);
    $when = ['vertical' => 'entrenamiento', 'goal' => 'hipertrofia', 'level' => 'intermedio', 'days' => 5];

    $result = $this->matcher->evaluate($when, $profile);
    expect($result['matched'])->toBeTrue();
    expect($result['matched_conditions'])->toBe([
        'vertical' => 'entrenamiento',
        'goal' => 'hipertrofia',
        'level' => 'intermedio',
        'days' => 5,
    ]);
});

it('NO matchea si una key del when no está en el profile', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento'); // sin goal
    $when = ['vertical' => 'entrenamiento', 'goal' => 'hipertrofia'];

    $result = $this->matcher->evaluate($when, $profile);
    expect($result['matched'])->toBeFalse();
});

it('NO matchea si un valor difiere', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', goal: 'fuerza');
    $when = ['vertical' => 'entrenamiento', 'goal' => 'hipertrofia'];

    $result = $this->matcher->evaluate($when, $profile);
    expect($result['matched'])->toBeFalse();
});

it('permite keys extra en el profile sin afectar match', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', goal: 'hipertrofia', level: 'intermedio', days: 5, gender: 'femenino');
    $when = ['vertical' => 'entrenamiento', 'goal' => 'hipertrofia']; // sin level/days/gender

    $result = $this->matcher->evaluate($when, $profile);
    expect($result['matched'])->toBeTrue();
});

it('match numérico es strict (1 == "1")', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', days: 5);
    $when = ['vertical' => 'entrenamiento', 'days' => '5']; // string en when, int en profile

    $result = $this->matcher->evaluate($when, $profile);
    expect($result['matched'])->toBeTrue(); // is_numeric ambos → coerce a float
});

it('when array contra profile array matchea si hay intersección', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', injuries: ['lumbalgia', 'lesion_hombro']);
    $when = ['vertical' => 'entrenamiento', 'injuries' => ['lumbalgia']];

    $result = $this->matcher->evaluate($when, $profile);
    expect($result['matched'])->toBeTrue();
});

it('when array contra profile escalar matchea si valor está en array (IN)', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', goal: 'recomposicion');
    $when = ['vertical' => 'entrenamiento', 'goal' => ['hipertrofia', 'recomposicion']];

    $result = $this->matcher->evaluate($when, $profile);
    expect($result['matched'])->toBeTrue();
});

it('when vacío matchea cualquier profile (zero condiciones)', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento');
    $when = [];

    $result = $this->matcher->evaluate($when, $profile);
    expect($result['matched'])->toBeTrue();
    expect($result['matched_conditions'])->toBe([]);
});

it('matched_conditions retorna actual values del profile (no expected del when)', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', goal: 'recomposicion');
    $when = ['vertical' => 'entrenamiento', 'goal' => ['hipertrofia', 'recomposicion']];

    $result = $this->matcher->evaluate($when, $profile);
    expect($result['matched_conditions']['goal'])->toBe('recomposicion');
});

it('ClientProfile::fromArray normaliza tipos', function () {
    $profile = ClientProfile::fromArray([
        'vertical' => 'entrenamiento',
        'days' => '5', // string
        'age' => '30',
        'weight_kg' => '75.5',
    ]);
    expect($profile->days)->toBe(5);
    expect($profile->age)->toBe(30);
    expect($profile->weightKg)->toBe(75.5);
});

it('ClientProfile::toArray omite null', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', goal: 'hipertrofia');
    expect($profile->toArray())->toBe([
        'vertical' => 'entrenamiento',
        'goal' => 'hipertrofia',
    ]);
});
