<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\FrequencyMatchesMethodologyValidator;

beforeEach(function () {
    $this->validator = new FrequencyMatchesMethodologyValidator(new JsonPathResolver());
});

function ctxForFrequency(array $plan): LintContext
{
    return new LintContext(
        plan: $plan,
        rule: new LintRuleMeta(
            code: 'heur_frequency_methodology_mismatch',
            vertical: 'entrenamiento',
            severity: 'error',
            description: 'mismatch frecuencia',
            checkType: 'heuristic',
            fixHintTemplate: 'revisar split',
            autoFixAvailable: false,
        ),
        checkDefinition: [],
        vertical: 'entrenamiento',
    );
}

it('plan PPL con 6 días NO produce violation (matches target_days_min/max=6)', function () {
    $plan = [
        'metodologia' => 'PPL (Push / Pull / Legs) 6 días',
        'frecuencia_dias' => 6,
    ];

    $violations = $this->validator->check(ctxForFrequency($plan));
    expect($violations)->toBeEmpty();
});

it('plan PPL con 4 días genera violation (PPL requiere 6)', function () {
    $plan = [
        'metodologia' => 'PPL (Push / Pull / Legs) 6 días',
        'frecuencia_dias' => 4,
    ];

    $violations = $this->validator->check(ctxForFrequency($plan));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('4 días/semana');
    expect($violations[0]->message)->toContain('6-6');
});

it('plan Body Part Split 5 días con 5 días NO produce violation', function () {
    $plan = [
        'metodologia' => 'Body Part Split 5 días',
        'frecuencia_dias' => 5,
    ];

    $violations = $this->validator->check(ctxForFrequency($plan));
    expect($violations)->toBeEmpty();
});

it('plan Body Part Split 5d con 3 días genera violation', function () {
    $plan = [
        'metodologia' => 'Body Part Split 5 días',
        'frecuencia_dias' => 3,
    ];

    $violations = $this->validator->check(ctxForFrequency($plan));
    expect($violations)->toHaveCount(1);
});

it('methodology no encontrada en DB no rompe (skip silencioso)', function () {
    $plan = [
        'metodologia' => 'Methodology Inventada XYZ',
        'frecuencia_dias' => 5,
    ];

    $violations = $this->validator->check(ctxForFrequency($plan));
    expect($violations)->toBeEmpty();
});

it('plan sin metodologia no rompe', function () {
    $plan = ['frecuencia_dias' => 5];
    $violations = $this->validator->check(ctxForFrequency($plan));
    expect($violations)->toBeEmpty();
});

it('plan sin frecuencia_dias no rompe', function () {
    $plan = ['metodologia' => 'PPL 6d'];
    $violations = $this->validator->check(ctxForFrequency($plan));
    expect($violations)->toBeEmpty();
});
