<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\PushPullBalanceValidator;

function ctxForPushPull(array $plan, float $maxRatio = 1.5, int $minSeries = 8): LintContext
{
    return new LintContext(
        plan: $plan,
        rule: new LintRuleMeta(
            code: 'heur_push_pull_imbalance',
            vertical: 'entrenamiento',
            severity: 'warning',
            description: 'push/pull imbalance',
            checkType: 'heuristic',
            fixHintTemplate: 'balanceá empuje y jalón',
            autoFixAvailable: false,
        ),
        checkDefinition: ['max_ratio' => $maxRatio, 'min_series' => $minSeries],
        vertical: 'entrenamiento',
    );
}

function makeEx(string $nombre, int $series): array
{
    return ['nombre' => $nombre, 'series' => $series, 'repeticiones' => '10', 'rir' => 2];
}

it('plan con push 16 series + pull 16 series NO produce violation (ratio 1.0)', function () {
    $validator = new PushPullBalanceValidator(new JsonPathResolver());
    $plan = ['semanas' => [['dias' => [
        ['ejercicios' => [
            makeEx('Press de banca con barra', 4), makeEx('Press de banca con mancuernas', 4), // push
            makeEx('Press militar con barra', 4), makeEx('Press inclinado con mancuernas', 4), // push
            makeEx('Dominadas', 4), makeEx('Remo con barra', 4), // pull
            makeEx('Jalón en polea alta', 4), makeEx('Remo con mancuerna a una mano', 4), // pull
        ]],
    ]]]];

    $violations = $validator->check(ctxForPushPull($plan));
    expect($violations)->toBeEmpty();
});

it('plan con push 20 + pull 5 (ratio 4.0) genera violation', function () {
    $validator = new PushPullBalanceValidator(new JsonPathResolver());
    $plan = ['semanas' => [['dias' => [
        ['ejercicios' => [
            makeEx('Press de banca con barra', 5), makeEx('Press de banca con mancuernas', 5),
            makeEx('Press militar con barra', 5), makeEx('Press inclinado con mancuernas', 5),
            makeEx('Dominadas', 5),
        ]],
    ]]]];

    $violations = $validator->check(ctxForPushPull($plan));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('push');
    expect($violations[0]->message)->toContain('pull');
});

it('ejercicios sin matching en exercise_metadata son ignorados', function () {
    $validator = new PushPullBalanceValidator(new JsonPathResolver());
    $plan = ['semanas' => [['dias' => [
        ['ejercicios' => [
            makeEx('Ejercicio Inventado A', 5),
            makeEx('Ejercicio Inventado B', 5),
        ]],
    ]]]];

    $violations = $validator->check(ctxForPushPull($plan));
    // Sin matching → push=0, pull=0 → ambos bajo min_series → skip
    expect($violations)->toBeEmpty();
});

it('plan sin semanas no rompe', function () {
    $validator = new PushPullBalanceValidator(new JsonPathResolver());
    $violations = $validator->check(ctxForPushPull(['semanas' => []]));
    expect($violations)->toBeEmpty();
});

it('threshold custom funciona', function () {
    $validator = new PushPullBalanceValidator(new JsonPathResolver());
    $plan = ['semanas' => [['dias' => [
        ['ejercicios' => [
            makeEx('Press de banca con barra', 4),
            makeEx('Press de banca con mancuernas', 4),
            makeEx('Press militar con barra', 3), // 11 push
            makeEx('Dominadas', 4),
            makeEx('Remo con barra', 4), // 8 pull
        ]],
    ]]]];

    // Default 1.5: 11/8 = 1.375 → no violation
    $violations = $validator->check(ctxForPushPull($plan, maxRatio: 1.5));
    expect($violations)->toBeEmpty();

    // Threshold 1.2: 1.375 > 1.2 → violation
    $violations = $validator->check(ctxForPushPull($plan, maxRatio: 1.2));
    expect($violations)->toHaveCount(1);
});

it('ambos lados bajo min_series no genera violation', function () {
    $validator = new PushPullBalanceValidator(new JsonPathResolver());
    $plan = ['semanas' => [['dias' => [
        ['ejercicios' => [
            makeEx('Press de banca con barra', 1), // 1 push
            makeEx('Dominadas', 1), // 1 pull
        ]],
    ]]]];

    $violations = $validator->check(ctxForPushPull($plan));
    expect($violations)->toBeEmpty();
});
