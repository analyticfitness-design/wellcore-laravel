<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\ProgressionAdequateValidator;

beforeEach(function () {
    $this->validator = new ProgressionAdequateValidator(new JsonPathResolver());
});

function ctxForProgression(array $plan, float $tolerance = 0.5): LintContext
{
    return new LintContext(
        plan: $plan,
        rule: new LintRuleMeta(
            code: 'heur_progression_inverted',
            vertical: 'entrenamiento',
            severity: 'warning',
            description: 'progresión invertida',
            checkType: 'heuristic',
            fixHintTemplate: 'baja el RIR',
            autoFixAvailable: false,
        ),
        checkDefinition: ['tolerance' => $tolerance],
        vertical: 'entrenamiento',
    );
}

function makeWeek(int $num, string $fase, int $rir): array
{
    return [
        'numero' => $num,
        'fase' => "$fase · RIR $rir",
        'dias' => [
            ['ejercicios' => [
                ['rir' => $rir],
                ['rir' => $rir],
                ['rir' => $rir],
            ]],
        ],
    ];
}

it('progresión lineal correcta (RIR baja) NO produce violation', function () {
    $plan = [
        'semanas' => [
            makeWeek(1, 'Adaptación', 3),
            makeWeek(2, 'Hipertrofia', 2),
            makeWeek(3, 'Fuerza', 1),
            makeWeek(4, 'Peak', 0),
        ],
    ];

    $violations = $this->validator->check(ctxForProgression($plan));
    expect($violations)->toBeEmpty();
});

it('detecta progresión invertida (RIR sube)', function () {
    $plan = [
        'semanas' => [
            makeWeek(1, 'Adaptación', 1),
            makeWeek(2, 'Hipertrofia', 3),  // RIR sube de 1 a 3 → MAL
        ],
    ];

    $violations = $this->validator->check(ctxForProgression($plan));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('Progresión invertida');
    expect($violations[0]->message)->toContain('semana 2');
});

it('NO genera violation si la siguiente semana es Deload', function () {
    $plan = [
        'semanas' => [
            makeWeek(1, 'Hipertrofia', 1),
            makeWeek(2, 'Deload', 3),  // Deload con RIR alto es esperado
        ],
    ];

    $violations = $this->validator->check(ctxForProgression($plan));
    expect($violations)->toBeEmpty();
});

it('NO genera violation si la siguiente semana es Recuperación', function () {
    $plan = [
        'semanas' => [
            makeWeek(1, 'Peak', 0),
            makeWeek(2, 'Recuperación', 3),
        ],
    ];

    $violations = $this->validator->check(ctxForProgression($plan));
    expect($violations)->toBeEmpty();
});

it('RIR igual entre semanas es aceptable (mantiene)', function () {
    $plan = [
        'semanas' => [
            makeWeek(1, 'Hipertrofia', 2),
            makeWeek(2, 'Hipertrofia', 2),
        ],
    ];

    $violations = $this->validator->check(ctxForProgression($plan));
    expect($violations)->toBeEmpty();
});

it('aumento dentro de tolerance no produce violation', function () {
    $plan = [
        'semanas' => [
            // Average RIR primera semana: 2.0
            ['fase' => 'A', 'dias' => [['ejercicios' => [['rir' => 2], ['rir' => 2]]]]],
            // Average RIR segunda semana: 2.5 → diff 0.5 (en threshold default)
            ['fase' => 'B', 'dias' => [['ejercicios' => [['rir' => 2], ['rir' => 3]]]]],
        ],
    ];

    $violations = $this->validator->check(ctxForProgression($plan, tolerance: 0.5));
    // diff 0.5 está en threshold → no violation (only strictly greater)
    expect($violations)->toBeEmpty();
});

it('detecta múltiples regresiones en plan largo', function () {
    $plan = [
        'semanas' => [
            makeWeek(1, 'A', 1),
            makeWeek(2, 'B', 3),  // regresión 1
            makeWeek(3, 'C', 1),
            makeWeek(4, 'D', 3),  // regresión 2
        ],
    ];

    $violations = $this->validator->check(ctxForProgression($plan));
    expect($violations)->toHaveCount(2);
});

it('plan con 1 sola semana no produce violation (no hay par)', function () {
    $plan = ['semanas' => [makeWeek(1, 'A', 2)]];
    $violations = $this->validator->check(ctxForProgression($plan));
    expect($violations)->toBeEmpty();
});

it('semana sin ejercicios o RIRs no rompe el validator', function () {
    $plan = [
        'semanas' => [
            makeWeek(1, 'A', 2),
            ['fase' => 'B', 'dias' => []], // sin ejercicios
        ],
    ];
    $violations = $this->validator->check(ctxForProgression($plan));
    expect($violations)->toBeEmpty();
});

it('RIR no numérico (ej. "1-2") es ignorado pero no rompe', function () {
    $plan = [
        'semanas' => [
            makeWeek(1, 'A', 2),
            ['fase' => 'B', 'dias' => [['ejercicios' => [['rir' => '1-2']]]]],
        ],
    ];
    // El validator solo procesa numerics. "1-2" es string → null avg → skip.
    $violations = $this->validator->check(ctxForProgression($plan));
    expect($violations)->toBeEmpty();
});
