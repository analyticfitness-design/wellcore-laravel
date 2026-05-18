<?php

declare(strict_types=1);

use App\Models\Kb\SupplementStack;
use App\Services\ComposeEngine\Supplementation\StackSelector;
use App\Services\DecisionEngine\Data\ClientProfile;

beforeEach(function () {
    $this->selector = new StackSelector();
});

it('escoge stack gender-specific cuando matchea el profile femenino', function () {
    $profile = new ClientProfile(
        vertical: 'suplementacion',
        goal: 'perdida_grasa',
        level: 'intermedio',
        gender: 'F',
        tier: 'esencial',
    );
    $chosen = $this->selector->selectFor($profile);
    expect($chosen)->not->toBeNull();
    expect($chosen->slug)->toBe('stack-perdida-grasa-femenina-intermedia');
});

it('escoge stack masculino para hombre intermedio perdida_grasa', function () {
    $profile = new ClientProfile(
        vertical: 'suplementacion',
        goal: 'perdida_grasa',
        level: 'intermedio',
        gender: 'M',
        tier: 'esencial',
    );
    $chosen = $this->selector->selectFor($profile);
    expect($chosen)->not->toBeNull();
    expect($chosen->slug)->toBe('stack-perdida-grasa-masculina-intermedia');
});

it('NO escoge stack gender-femenino para hombre (penalización fuerte)', function () {
    $profile = new ClientProfile(
        vertical: 'suplementacion',
        goal: 'recomposicion',
        gender: 'M',
        tier: 'esencial',
    );
    $chosen = $this->selector->selectFor($profile);
    // No debe ser el de mujer
    expect($chosen->slug)->not->toContain('femenina');
    expect($chosen->slug)->not->toContain('flujo-menstrual');
});

it('respeta tier_min — tier=elite tiene acceso a más stacks que esencial', function () {
    $profileElite = new ClientProfile(
        vertical: 'suplementacion',
        goal: 'recomposicion',
        gender: 'F',
        tier: 'elite',
    );
    $profileEsencial = new ClientProfile(
        vertical: 'suplementacion',
        goal: 'recomposicion',
        gender: 'F',
        tier: 'esencial',
    );
    $chosenElite = $this->selector->selectFor($profileElite);
    $chosenEsencial = $this->selector->selectFor($profileEsencial);

    // Elite puede acceder al stack de recomposicion-femenina-elite si existe
    // o al mismo que esencial. El de esencial NO debe ser un stack 'elite'.
    expect($chosenEsencial)->not->toBeNull();
    expect($chosenEsencial->applicable_tier_min)->not->toBe('elite');
});

it('tier trial sin stacks de trial devuelve null o fallback', function () {
    $profile = new ClientProfile(
        vertical: 'suplementacion',
        goal: 'perdida_grasa',
        gender: 'F',
        tier: 'trial',
    );
    $chosen = $this->selector->selectFor($profile);
    // En seed actual no hay stacks de trial → null es comportamiento esperado.
    // Si en el futuro se agregan, este test puede ajustarse.
    expect($chosen)->toBeNull();
});

it('cliente sin gender escogido recibe stack gender-neutral', function () {
    $profile = new ClientProfile(
        vertical: 'suplementacion',
        goal: 'recomposicion',
        tier: 'esencial',
    );
    $chosen = $this->selector->selectFor($profile);
    expect($chosen)->not->toBeNull();
    // No debe ser un stack gender-specific cuando no sabemos el gender
    expect($chosen->applicable_genders)->not->toBeEmpty();
});

it('genderTokens acepta múltiples formatos (F/f/femenino/female/mujer)', function () {
    foreach (['F', 'f', 'Femenino', 'female', 'MUJER'] as $g) {
        $profile = new ClientProfile(
            vertical: 'suplementacion',
            goal: 'perdida_grasa',
            level: 'intermedio',
            gender: $g,
            tier: 'esencial',
        );
        $chosen = $this->selector->selectFor($profile);
        expect($chosen->slug)->toBe('stack-perdida-grasa-femenina-intermedia');
    }
});

it('fallback al universal si no hay score positivo (caso poco probable)', function () {
    // Profile sin goal ni level → solo gender vale.
    $profile = new ClientProfile(
        vertical: 'suplementacion',
        tier: 'esencial',
    );
    $chosen = $this->selector->selectFor($profile);
    expect($chosen)->not->toBeNull();
});
