<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\CreatinaMissingValidator;

beforeEach(function () {
    $this->validator = new CreatinaMissingValidator(new JsonPathResolver());
});

function ctxForCreatina(array $plan): LintContext
{
    return new LintContext(
        plan: $plan,
        rule: new LintRuleMeta(
            code: 'heur_supl_creatina_missing',
            vertical: 'suplementacion',
            severity: 'warning',
            description: 'creatina missing',
            checkType: 'heuristic',
            fixHintTemplate: 'agregar creatina',
            autoFixAvailable: false,
        ),
        checkDefinition: [],
        vertical: 'suplementacion',
    );
}

it('plan supl con creatina (slug=creatina-monohidrato) NO genera violation', function () {
    $plan = ['plan_type' => 'suplementacion', 'suplementos' => [
        ['slug' => 'creatina-monohidrato', 'nombre' => 'Creatina monohidrato'],
        ['slug' => 'whey', 'nombre' => 'Whey concentrada'],
    ]];
    expect($this->validator->check(ctxForCreatina($plan)))->toBeEmpty();
});

it('plan supl con creatina (nombre solamente) NO genera violation', function () {
    $plan = ['plan_type' => 'suplementacion', 'suplementos' => [
        ['slug' => 'cualquier-slug', 'nombre' => 'Creatina HCl premium'],
    ]];
    expect($this->validator->check(ctxForCreatina($plan)))->toBeEmpty();
});

it('plan supl SIN creatina genera violation', function () {
    $plan = ['plan_type' => 'suplementacion', 'suplementos' => [
        ['slug' => 'whey', 'nombre' => 'Whey'],
        ['slug' => 'multivitaminico', 'nombre' => 'Multivitamínico'],
    ]];
    $violations = $this->validator->check(ctxForCreatina($plan));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('creatina');
});

it('plan supl vacío genera violation', function () {
    $plan = ['plan_type' => 'suplementacion', 'suplementos' => []];
    expect($this->validator->check(ctxForCreatina($plan)))->toHaveCount(1);
});

it('NO aplica a plan_type=entrenamiento', function () {
    $plan = ['plan_type' => 'entrenamiento', 'suplementos' => []];
    expect($this->validator->check(ctxForCreatina($plan)))->toBeEmpty();
});

it('NO aplica a plan_type=nutricion', function () {
    $plan = ['plan_type' => 'nutricion'];
    expect($this->validator->check(ctxForCreatina($plan)))->toBeEmpty();
});

it('case-insensitive: "CREATINA" mayúscula detecta', function () {
    $plan = ['plan_type' => 'suplementacion', 'suplementos' => [
        ['slug' => 'algo', 'nombre' => 'CREATINA MONOHIDRATO 5g'],
    ]];
    expect($this->validator->check(ctxForCreatina($plan)))->toBeEmpty();
});

it('detecta substring "creatina" en slug', function () {
    $plan = ['plan_type' => 'suplementacion', 'suplementos' => [
        ['slug' => 'creatina-hcl', 'nombre' => 'Creatina HCl'],
    ]];
    expect($this->validator->check(ctxForCreatina($plan)))->toBeEmpty();
});
