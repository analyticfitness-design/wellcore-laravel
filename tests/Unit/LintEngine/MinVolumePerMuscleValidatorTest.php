<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\MinVolumePerMuscleValidator;

beforeEach(function () {
    $this->validator = new MinVolumePerMuscleValidator(new JsonPathResolver());
});

function ctxForMinVolume(array $plan, int $minSeries = 10): LintContext
{
    return new LintContext(
        plan: $plan,
        rule: new LintRuleMeta(
            code: 'heur_min_volume_per_muscle',
            vertical: 'entrenamiento',
            severity: 'warning',
            description: 'undertraining',
            checkType: 'heuristic',
            fixHintTemplate: 'agregar series',
            autoFixAvailable: false,
        ),
        checkDefinition: ['min_series_per_week' => $minSeries],
        vertical: 'entrenamiento',
    );
}

function makeMVDay(string $grupo, int $series, int $ejercicios): array
{
    $ejs = [];
    for ($i = 0; $i < $ejercicios; $i++) {
        $ejs[] = ['series' => $series, 'repeticiones' => '10'];
    }
    return ['dia_semana' => 'X', 'grupo_muscular' => $grupo, 'ejercicios' => $ejs];
}

it('pecho con 4×3 series = 12 series no produce violation (>= 10)', function () {
    $plan = ['semanas' => [['dias' => [makeMVDay('Pecho', 4, 3)]]]];
    $violations = $this->validator->check(ctxForMinVolume($plan));
    expect($violations)->toBeEmpty();
});

it('pecho con solo 2×3 series = 6 series produce violation', function () {
    $plan = ['semanas' => [['dias' => [makeMVDay('Pecho', 2, 3)]]]];
    $violations = $this->validator->check(ctxForMinVolume($plan));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('pecho');
    expect($violations[0]->message)->toContain('6.0 series');
});

it('detecta múltiples grupos undertrained en un mismo plan', function () {
    $plan = ['semanas' => [['dias' => [
        makeMVDay('Pecho', 2, 2),        // 4 series → undertrained
        makeMVDay('Espalda', 2, 2),      // 4 series → undertrained
        makeMVDay('Cuádriceps', 4, 4),   // 16 series → ok
    ]]]];
    $violations = $this->validator->check(ctxForMinVolume($plan));
    expect($violations)->toHaveCount(2); // pecho + espalda
});

it('Core/Cardio/Antebrazos NO aplican la regla (no son mayores)', function () {
    $plan = ['semanas' => [['dias' => [
        makeMVDay('Core', 1, 1),         // 1 serie — pero Core no aplica
        makeMVDay('Cardiovascular', 1, 1),
    ]]]];
    $violations = $this->validator->check(ctxForMinVolume($plan));
    expect($violations)->toBeEmpty();
});

it('threshold custom funciona (min=15)', function () {
    $plan = ['semanas' => [['dias' => [makeMVDay('Pecho', 4, 3)]]]];  // 12 series
    $violations = $this->validator->check(ctxForMinVolume($plan, minSeries: 15));
    expect($violations)->toHaveCount(1); // 12 < 15
});

it('día con múltiples grupos divide series proporcionalmente', function () {
    $plan = ['semanas' => [['dias' => [
        makeMVDay('Pecho + Tríceps', 4, 4), // 16 series totales / 2 grupos = 8 c/u
    ]]]];
    $violations = $this->validator->check(ctxForMinVolume($plan));
    // Ambos quedan en 8 series → ambos < 10 → ambos warning
    expect($violations)->toHaveCount(2);
});

it('plan sin semanas no produce violation', function () {
    $violations = $this->validator->check(ctxForMinVolume(['semanas' => []]));
    expect($violations)->toBeEmpty();
});

it('grupo NO trabajado (cero ejercicios) NO genera violation por ausencia', function () {
    // Solo entreno espalda — no menciono pecho → no debería disparar warning por pecho
    $plan = ['semanas' => [['dias' => [makeMVDay('Espalda', 4, 4)]]]];
    $violations = $this->validator->check(ctxForMinVolume($plan));
    // Solo espalda (que tiene 16 series, ok). Pecho no existe en plan → no warning.
    expect($violations)->toBeEmpty();
});
