<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\MacrosCoherenciaValidator;

beforeEach(function () {
    $this->validator = new MacrosCoherenciaValidator(new JsonPathResolver());
});

function ctxMacros(array $plan, float $tolerance = 5.0): LintContext
{
    return new LintContext(
        plan: $plan,
        rule: new LintRuleMeta(
            code: 'heur_macros_coherencia',
            vertical: 'nutricion',
            severity: 'warning',
            description: 'macros coherencia',
            checkType: 'heuristic',
            fixHintTemplate: 'revisar macros',
            autoFixAvailable: false,
        ),
        checkDefinition: ['rule' => 'macros_coherencia', 'tolerance_pct' => $tolerance],
        vertical: 'nutricion',
    );
}

it('macros coherentes dentro del 5% NO violan: 2000 kcal con 150P + 200C + 67G (~ 2003)', function () {
    // 150*4 + 200*4 + 67*9 = 600 + 800 + 603 = 2003 → ~0.15% drift
    $plan = ['plan_type' => 'nutricion', 'macros' => [
        'kcal' => 2000, 'proteina_g' => 150, 'carbohidratos_g' => 200, 'grasa_g' => 67,
    ]];
    expect($this->validator->check(ctxMacros($plan)))->toBeEmpty();
});

it('macros incoherentes >5%: kcal=2000 con P=100 + C=100 + G=20 = 980 → 51% drift', function () {
    $plan = ['plan_type' => 'nutricion', 'macros' => [
        'kcal' => 2000, 'proteina_g' => 100, 'carbohidratos_g' => 100, 'grasa_g' => 20,
    ]];
    $v = $this->validator->check(ctxMacros($plan));
    expect($v)->toHaveCount(1);
    expect($v[0]->message)->toContain('Macros incoherentes');
});

it('acepta alias carbs_g (no carbohidratos_g)', function () {
    $plan = ['plan_type' => 'nutricion', 'macros' => [
        'kcal' => 2000, 'proteina_g' => 150, 'carbs_g' => 200, 'grasa_g' => 67,
    ]];
    expect($this->validator->check(ctxMacros($plan)))->toBeEmpty();
});

it('acepta alias grasas_g', function () {
    $plan = ['plan_type' => 'nutricion', 'macros' => [
        'kcal' => 2000, 'proteina_g' => 150, 'carbohidratos_g' => 200, 'grasas_g' => 67,
    ]];
    expect($this->validator->check(ctxMacros($plan)))->toBeEmpty();
});

it('skip si falta cualquier macro', function () {
    $plan = ['plan_type' => 'nutricion', 'macros' => ['kcal' => 2000, 'proteina_g' => 150]];
    expect($this->validator->check(ctxMacros($plan)))->toBeEmpty();
});

it('NO aplica a plan_type=entrenamiento', function () {
    $plan = ['plan_type' => 'entrenamiento', 'macros' => ['kcal' => 2000, 'proteina_g' => 1, 'carbohidratos_g' => 1, 'grasa_g' => 1]];
    expect($this->validator->check(ctxMacros($plan)))->toBeEmpty();
});

it('tolerance configurable: 1% rechaza el caso ~0.15% pasa, ~3% no pasa', function () {
    // 150*4 + 200*4 + 65*9 = 600+800+585 = 1985 → 0.75% drift
    $planClose = ['plan_type' => 'nutricion', 'macros' => [
        'kcal' => 2000, 'proteina_g' => 150, 'carbohidratos_g' => 200, 'grasa_g' => 65,
    ]];
    expect($this->validator->check(ctxMacros($planClose, tolerance: 1.0)))->toBeEmpty();

    // 150*4 + 200*4 + 50*9 = 600+800+450 = 1850 → 7.5% drift > 1%
    $planFar = ['plan_type' => 'nutricion', 'macros' => [
        'kcal' => 2000, 'proteina_g' => 150, 'carbohidratos_g' => 200, 'grasa_g' => 50,
    ]];
    expect($this->validator->check(ctxMacros($planFar, tolerance: 1.0)))->toHaveCount(1);
});
