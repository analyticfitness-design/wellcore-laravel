<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\WarmupMissingValidator;

beforeEach(function () {
    $this->validator = new WarmupMissingValidator(new JsonPathResolver());
});

function ctxForWarmup(array $plan): LintContext
{
    return new LintContext(
        plan: $plan,
        rule: new LintRuleMeta(
            code: 'heur_warmup_missing',
            vertical: 'entrenamiento',
            severity: 'warning',
            description: 'warmup',
            checkType: 'heuristic',
            fixHintTemplate: 'agregar warmup',
            autoFixAvailable: false,
        ),
        checkDefinition: [],
        vertical: 'entrenamiento',
    );
}

it('plan con calentamiento top-level NO genera violation', function () {
    $plan = ['calentamiento' => '5 min bicicleta + 10 movilidades articulares'];
    expect($this->validator->check(ctxForWarmup($plan)))->toBeEmpty();
});

it('plan con calentamiento per-día NO genera violation', function () {
    $plan = [
        'semanas' => [['dias' => [['calentamiento' => '5 min cardio']]]],
    ];
    expect($this->validator->check(ctxForWarmup($plan)))->toBeEmpty();
});

it('plan con tip que menciona calentamiento NO genera violation', function () {
    $plan = ['tips' => ['Calentá 5-10 min antes de cada sesión']];
    expect($this->validator->check(ctxForWarmup($plan)))->toBeEmpty();
});

it('plan con notas_coach que menciona warmup NO genera violation', function () {
    $plan = ['notas_coach' => 'Hacé warmup específico antes de los compuestos.'];
    expect($this->validator->check(ctxForWarmup($plan)))->toBeEmpty();
});

it('plan SIN mención de calentamiento en ningún path genera violation', function () {
    $plan = [
        'titulo' => 'Plan X',
        'tips' => ['Hidratate', 'Dormí 7-9h'],
        'notas_coach' => 'Anotá pesos y reps.',
        'semanas' => [['dias' => [['ejercicios' => []]]]],
    ];
    $violations = $this->validator->check(ctxForWarmup($plan));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('calentamiento');
});

it('plan vacío genera violation (no menciona warmup en ningún lado)', function () {
    expect($this->validator->check(ctxForWarmup([])))->toHaveCount(1);
});

it('keyword "calienta" también es detectada', function () {
    $plan = ['tips' => ['Calienta 5 min antes de levantar']];
    expect($this->validator->check(ctxForWarmup($plan)))->toBeEmpty();
});

it('keyword "movilidad articular" también es detectada', function () {
    $plan = ['notas_coach' => 'Movilidad articular antes de empezar.'];
    expect($this->validator->check(ctxForWarmup($plan)))->toBeEmpty();
});

it('case-insensitive: "Warm-Up" también detecta', function () {
    $plan = ['tips' => ['Warm-Up de 5 min en bicicleta']];
    expect($this->validator->check(ctxForWarmup($plan)))->toBeEmpty();
});
