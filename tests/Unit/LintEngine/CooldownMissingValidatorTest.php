<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\CooldownMissingValidator;

beforeEach(function () {
    $this->validator = new CooldownMissingValidator(new JsonPathResolver());
});

function ctxCooldown(array $plan): LintContext
{
    return new LintContext(
        plan: $plan,
        rule: new LintRuleMeta(
            code: 'heur_cooldown_missing',
            vertical: 'entrenamiento',
            severity: 'warning',
            description: 'cooldown missing',
            checkType: 'heuristic',
            fixHintTemplate: 'agregar vuelta a la calma',
            autoFixAvailable: false,
        ),
        checkDefinition: ['rule' => 'cooldown_missing'],
        vertical: 'entrenamiento',
    );
}

it('plan entrenamiento con vuelta_calma top-level NO viola', function () {
    $plan = ['plan_type' => 'entrenamiento', 'vuelta_calma' => '5 min estiramiento + caminata'];
    expect($this->validator->check(ctxCooldown($plan)))->toBeEmpty();
});

it('plan entrenamiento con cooldown en día NO viola', function () {
    $plan = ['plan_type' => 'entrenamiento', 'semanas' => [
        ['dias' => [['cooldown' => 'caminata 5 min']]],
    ]];
    expect($this->validator->check(ctxCooldown($plan)))->toBeEmpty();
});

it('plan entrenamiento con mención "estiramiento final" en tips NO viola', function () {
    $plan = ['plan_type' => 'entrenamiento', 'tips' => ['Cerrá con estiramiento final 5 min']];
    expect($this->validator->check(ctxCooldown($plan)))->toBeEmpty();
});

it('plan entrenamiento con "bajar pulsaciones" en notas_coach NO viola', function () {
    $plan = ['plan_type' => 'entrenamiento', 'notas_coach' => 'Cerrá con 5 min para bajar pulsaciones'];
    expect($this->validator->check(ctxCooldown($plan)))->toBeEmpty();
});

it('plan entrenamiento sin nada genera violation', function () {
    $plan = ['plan_type' => 'entrenamiento', 'objetivo' => 'fuerza'];
    expect($this->validator->check(ctxCooldown($plan)))->toHaveCount(1);
});

it('NO aplica a plan_type=nutricion', function () {
    $plan = ['plan_type' => 'nutricion'];
    expect($this->validator->check(ctxCooldown($plan)))->toBeEmpty();
});

it('keyword "enfriamiento" en tips NO viola', function () {
    $plan = ['plan_type' => 'entrenamiento', 'tips' => ['Hacé enfriamiento de 3 min']];
    expect($this->validator->check(ctxCooldown($plan)))->toBeEmpty();
});
