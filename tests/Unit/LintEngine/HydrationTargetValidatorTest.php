<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\HydrationTargetValidator;

beforeEach(function () {
    $this->validator = new HydrationTargetValidator(new JsonPathResolver());
});

function ctxHydration(array $plan): LintContext
{
    return new LintContext(
        plan: $plan,
        rule: new LintRuleMeta(
            code: 'heur_hydration_target',
            vertical: 'nutricion',
            severity: 'warning',
            description: 'hydration target',
            checkType: 'heuristic',
            fixHintTemplate: 'agregar hidratacion_ml_dia',
            autoFixAvailable: false,
        ),
        checkDefinition: ['rule' => 'hydration_target'],
        vertical: 'nutricion',
    );
}

it('plan nutricion SIN target genera violation', function () {
    $plan = ['plan_type' => 'nutricion', 'macros' => ['kcal' => 2000]];
    expect($this->validator->check(ctxHydration($plan)))->toHaveCount(1);
});

it('plan nutricion con macros.hidratacion_ml_dia OK no genera violation', function () {
    $plan = ['plan_type' => 'nutricion', 'macros' => ['kcal' => 2000, 'hidratacion_ml_dia' => 2500]];
    expect($this->validator->check(ctxHydration($plan)))->toBeEmpty();
});

it('detecta mención "2.5 litros" en tips', function () {
    $plan = ['plan_type' => 'nutricion', 'tips' => ['Tomá 2.5 litros de agua al día']];
    expect($this->validator->check(ctxHydration($plan)))->toBeEmpty();
});

it('detecta mención "2500 ml" en tips', function () {
    $plan = ['plan_type' => 'nutricion', 'tips' => ['Apuntá a 2500 ml diarios']];
    expect($this->validator->check(ctxHydration($plan)))->toBeEmpty();
});

it('detecta "8 vasos" en tips (8 × 250 = 2000 ml)', function () {
    $plan = ['plan_type' => 'nutricion', 'tips' => ['Tomá 8 vasos de agua']];
    expect($this->validator->check(ctxHydration($plan)))->toBeEmpty();
});

it('subhidratación con peso: 1000 ml para 70 kg = 14 ml/kg genera violation', function () {
    $plan = ['plan_type' => 'nutricion', 'macros' => ['kcal' => 2000, 'hidratacion_ml_dia' => 1000, 'peso_kg' => 70]];
    $v = $this->validator->check(ctxHydration($plan));
    expect($v)->toHaveCount(1);
    expect($v[0]->message)->toContain('14.3 ml/kg');
});

it('hidratación adecuada para peso: 2500 ml para 70 kg = 35.7 ml/kg NO genera violation', function () {
    $plan = ['plan_type' => 'nutricion', 'macros' => ['kcal' => 2000, 'hidratacion_ml_dia' => 2500, 'peso_kg' => 70]];
    expect($this->validator->check(ctxHydration($plan)))->toBeEmpty();
});

it('NO aplica a plan_type=entrenamiento', function () {
    $plan = ['plan_type' => 'entrenamiento'];
    expect($this->validator->check(ctxHydration($plan)))->toBeEmpty();
});
