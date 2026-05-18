<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\VolumeBalancePerMuscleValidator;

beforeEach(function () {
    $this->validator = new VolumeBalancePerMuscleValidator(new JsonPathResolver());
});

function ctxForVolumeBalance(array $plan, float $maxRatio = 2.0): LintContext
{
    return new LintContext(
        plan: $plan,
        rule: new LintRuleMeta(
            code: 'heur_volume_imbalance',
            vertical: 'entrenamiento',
            severity: 'warning',
            description: 'desbalance',
            checkType: 'heuristic',
            fixHintTemplate: 'balanceá',
            autoFixAvailable: false,
        ),
        checkDefinition: ['max_ratio' => $maxRatio, 'min_series_per_group' => 4],
        vertical: 'entrenamiento',
    );
}

function makeDay(string $grupo, int $series, int $ejercicios): array
{
    $ejs = [];
    for ($i = 0; $i < $ejercicios; $i++) {
        $ejs[] = ['series' => $series, 'repeticiones' => '10'];
    }
    return ['dia_semana' => 'X', 'grupo_muscular' => $grupo, 'ejercicios' => $ejs];
}

it('plan balanceado pecho/espalda no produce violation', function () {
    $plan = [
        'semanas' => [[
            'dias' => [
                makeDay('Pecho + Tríceps', 4, 4),  // 16 series → 8 pecho + 8 triceps
                makeDay('Espalda + Bíceps', 4, 4), // 16 series → 8 espalda + 8 biceps
            ],
        ]],
    ];

    $violations = $this->validator->check(ctxForVolumeBalance($plan));
    expect($violations)->toBeEmpty();
});

it('detecta desbalance pecho >> espalda', function () {
    $plan = [
        'semanas' => [[
            'dias' => [
                makeDay('Pecho', 5, 4),     // 20 series pecho
                makeDay('Pecho', 5, 4),     // 20 más
                makeDay('Espalda', 3, 1),   // solo 3
            ],
        ]],
    ];

    $violations = $this->validator->check(ctxForVolumeBalance($plan));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('pecho');
    expect($violations[0]->message)->toContain('espalda');
});

it('detecta desbalance cuádriceps >> isquiotibiales', function () {
    $plan = [
        'semanas' => [[
            'dias' => [
                makeDay('Cuádriceps', 4, 5),   // 20 series cuad
                makeDay('Cuádriceps', 4, 5),   // 20 más
                makeDay('Isquiotibial', 3, 1), // 3 series isquio
            ],
        ]],
    ];

    $violations = $this->validator->check(ctxForVolumeBalance($plan));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('cuadriceps');
});

it('detecta desbalance bíceps >> tríceps', function () {
    $plan = [
        'semanas' => [[
            'dias' => [
                makeDay('Bíceps', 3, 6),    // 18 series biceps
                makeDay('Bíceps', 3, 4),    // 12 más
                makeDay('Tríceps', 3, 1),   // 3 triceps
            ],
        ]],
    ];

    $violations = $this->validator->check(ctxForVolumeBalance($plan));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('biceps');
});

it('ignora pares antagonistas con menos del minimo de series', function () {
    $plan = [
        'semanas' => [[
            'dias' => [
                makeDay('Pecho', 1, 1),    // 1 series pecho
                makeDay('Espalda', 1, 1),  // 1 espalda → ambos bajo min_series_per_group=4
            ],
        ]],
    ];

    $violations = $this->validator->check(ctxForVolumeBalance($plan));
    expect($violations)->toBeEmpty();
});

it('ratio exactamente al threshold NO produce violation (sin estrictamente mayor)', function () {
    $plan = [
        'semanas' => [[
            'dias' => [
                makeDay('Pecho', 4, 2),    // 8 series pecho
                makeDay('Espalda', 4, 1),  // 4 espalda → ratio 2.0
            ],
        ]],
    ];

    $violations = $this->validator->check(ctxForVolumeBalance($plan, maxRatio: 2.0));
    expect($violations)->toBeEmpty();
});

it('día con múltiples grupos divide las series proporcionalmente', function () {
    $plan = [
        'semanas' => [[
            'dias' => [
                // 10 series totales / 2 grupos = 5 por grupo
                makeDay('Pecho + Tríceps', 5, 2),
                makeDay('Espalda + Bíceps', 5, 2),
            ],
        ]],
    ];

    $violations = $this->validator->check(ctxForVolumeBalance($plan));
    expect($violations)->toBeEmpty();
});

it('plan sin semanas no genera violation', function () {
    $violations = $this->validator->check(ctxForVolumeBalance(['semanas' => []]));
    expect($violations)->toBeEmpty();
});

it('plan sin dias[] en semana no genera violation', function () {
    $violations = $this->validator->check(ctxForVolumeBalance(['semanas' => [['numero' => 1]]]));
    expect($violations)->toBeEmpty();
});

it('plan sin grupos antagonistas (solo glúteo/hombros) no genera violation', function () {
    $plan = [
        'semanas' => [[
            'dias' => [
                makeDay('Glúteo + Cardio', 4, 4),
                makeDay('Hombros', 4, 4),
                makeDay('Core', 3, 3),
            ],
        ]],
    ];

    $violations = $this->validator->check(ctxForVolumeBalance($plan));
    expect($violations)->toBeEmpty();
});
