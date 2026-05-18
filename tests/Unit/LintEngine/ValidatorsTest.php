<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\AllowedValuesValidator;
use App\Services\LintEngine\Validators\ArrayNonEmptyValidator;
use App\Services\LintEngine\Validators\ArrayOfStringsValidator;
use App\Services\LintEngine\Validators\ExistsAndIntPositiveValidator;
use App\Services\LintEngine\Validators\ExistsAndNonEmptyValidator;
use App\Services\LintEngine\Validators\ExistsInEachValidator;
use App\Services\LintEngine\Validators\HasRequiredKeysValidator;
use App\Services\LintEngine\Validators\ObjectKeysNotInValidator;
use App\Services\LintEngine\Validators\ObjectWithKeysValidator;
use App\Services\LintEngine\Validators\PercentageSameSetsRepsValidator;
use App\Services\LintEngine\Validators\StartsWithValidator;
use App\Services\LintEngine\Validators\UrlMatchesPatternValidator;
use App\Services\LintEngine\Validators\WeeksAreIdenticalValidator;

beforeEach(function () {
    $this->resolver = new JsonPathResolver();
});

function ruleMeta(string $code = 'test_rule', string $severity = 'error', bool $autoFix = false): LintRuleMeta
{
    return new LintRuleMeta($code, null, $severity, 'desc', 'schema', 'fix hint', $autoFix);
}

function ctx(array $plan, array $checkDef, ?string $vertical = null): LintContext
{
    return new LintContext($plan, ruleMeta(), $checkDef, $vertical);
}

it('ExistsAndNonEmpty falla si path no existe', function () {
    $v = new ExistsAndNonEmptyValidator($this->resolver);
    $result = $v->check(ctx([], ['json_path' => '$.objetivo']));
    expect($result)->toHaveCount(1);
});

it('ExistsAndNonEmpty falla con string vacío', function () {
    $v = new ExistsAndNonEmptyValidator($this->resolver);
    $result = $v->check(ctx(['objetivo' => '   '], ['json_path' => '$.objetivo']));
    expect($result)->toHaveCount(1);
});

it('ExistsAndNonEmpty pasa con valor presente', function () {
    $v = new ExistsAndNonEmptyValidator($this->resolver);
    $result = $v->check(ctx(['objetivo' => 'Pérdida de grasa'], ['json_path' => '$.objetivo']));
    expect($result)->toHaveCount(0);
});

it('ExistsAndIntPositive falla con string no-numérico', function () {
    $v = new ExistsAndIntPositiveValidator($this->resolver);
    $result = $v->check(ctx(['objetivo_cal' => 'no-int'], ['json_path' => '$.objetivo_cal']));
    expect($result)->toHaveCount(1);
});

it('ExistsAndIntPositive pasa con int positivo', function () {
    $v = new ExistsAndIntPositiveValidator($this->resolver);
    $result = $v->check(ctx(['objetivo_cal' => 2400], ['json_path' => '$.objetivo_cal']));
    expect($result)->toHaveCount(0);
});

it('ObjectWithKeys falla si falta el objeto', function () {
    $v = new ObjectWithKeysValidator($this->resolver);
    $result = $v->check(ctx([], [
        'json_path' => '$.split',
        'required_keys_any_of' => ['Lunes', 'Martes'],
    ]));
    expect($result)->toHaveCount(1);
});

it('ObjectWithKeys pasa con al menos una key requerida', function () {
    $v = new ObjectWithKeysValidator($this->resolver);
    $result = $v->check(ctx(['split' => ['Lunes' => 'Pecho']], [
        'json_path' => '$.split',
        'required_keys_any_of' => ['Lunes', 'Martes'],
    ]));
    expect($result)->toHaveCount(0);
});

it('HasRequiredKeys reporta keys faltantes', function () {
    $v = new HasRequiredKeysValidator($this->resolver);
    $plan = ['semanas' => [['dias' => [['dia_semana' => 'Lunes']]]]];
    $result = $v->check(ctx($plan, [
        'json_path' => '$.semanas[*].dias[*]',
        'required_keys' => ['dia_semana', 'grupo_muscular'],
    ]));
    expect($result)->toHaveCount(1);
    expect($result[0]->message)->toContain('grupo_muscular');
});

it('ObjectKeysNotIn detecta forbidden_keys', function () {
    $v = new ObjectKeysNotInValidator($this->resolver);
    $plan = ['comidas' => [['macros' => ['proteina_g' => 30, 'carbohidratos' => 50]]]];
    $result = $v->check(ctx($plan, [
        'json_path' => '$.comidas[*].macros',
        'forbidden_keys' => ['proteina_g', 'carbohidratos_g'],
    ]));
    expect($result)->toHaveCount(1);
});

it('ExistsInEach detecta items con campo faltante', function () {
    $v = new ExistsInEachValidator($this->resolver);
    $plan = ['semanas' => [['fase' => 'Adaptación'], ['numero' => 2] /* falta fase */]];
    $result = $v->check(ctx($plan, ['json_path' => '$.semanas[*].fase']));
    expect($result)->toHaveCount(1);
    expect($result[0]->jsonPath)->toContain('semanas[1]');
});

it('StartsWith pasa con prefijo válido (incluso con sufijo)', function () {
    $v = new StartsWithValidator($this->resolver);
    $plan = ['semanas' => [['fase' => 'Adaptación · RIR 3']]];
    $result = $v->check(ctx($plan, [
        'json_path' => '$.semanas[*].fase',
        'allowed_values' => ['Adaptación', 'Hipertrofia'],
    ]));
    expect($result)->toHaveCount(0);
});

it('StartsWith falla con valor que no empieza con allowed', function () {
    $v = new StartsWithValidator($this->resolver);
    $plan = ['semanas' => [['fase' => 'adaptacion']]];
    $result = $v->check(ctx($plan, [
        'json_path' => '$.semanas[*].fase',
        'allowed_values' => ['Adaptación', 'Hipertrofia'],
    ]));
    expect($result)->toHaveCount(1);
});

it('ArrayOfStrings rechaza arrays con objetos', function () {
    $v = new ArrayOfStringsValidator($this->resolver);
    $plan = ['comidas' => [['opcion_a' => [['item' => 'Huevo', 'cantidad' => 3]]]]];
    $result = $v->check(ctx($plan, ['json_path' => '$.comidas[*].opcion_a']));
    expect($result)->toHaveCount(1);
});

it('ArrayOfStrings acepta arrays de strings', function () {
    $v = new ArrayOfStringsValidator($this->resolver);
    $plan = ['comidas' => [['opcion_a' => ['Huevos (3 und)', 'Avena (60g)']]]];
    $result = $v->check(ctx($plan, ['json_path' => '$.comidas[*].opcion_a']));
    expect($result)->toHaveCount(0);
});

it('ArrayNonEmpty falla con array vacío', function () {
    $v = new ArrayNonEmptyValidator($this->resolver);
    $result = $v->check(ctx(['suplementos' => []], ['json_path' => '$.suplementos']));
    expect($result)->toHaveCount(1);
});

it('AllowedValues rechaza valor no listado', function () {
    $v = new AllowedValuesValidator($this->resolver);
    $result = $v->check(ctx(['plan_type' => 'inventado'], [
        'json_path' => '$.plan_type',
        'allowed_values' => ['entrenamiento', 'nutricion'],
    ]));
    expect($result)->toHaveCount(1);
});

it('PercentageSameSetsReps detecta monotonía', function () {
    $v = new PercentageSameSetsRepsValidator($this->resolver);
    $plan = [
        'semanas' => [[
            'dias' => [[
                'ejercicios' => [
                    ['series' => 3, 'repeticiones' => '12'],
                    ['series' => 3, 'repeticiones' => '12'],
                    ['series' => 3, 'repeticiones' => '12'],
                ],
            ]],
        ]],
    ];
    $result = $v->check(ctx($plan, [
        'threshold_pct' => 60,
        'patterns' => [['series' => 3, 'reps' => '12']],
    ]));
    expect($result)->toHaveCount(1);
});

it('WeeksAreIdentical detecta semanas idénticas', function () {
    $v = new WeeksAreIdenticalValidator($this->resolver);
    $semana = ['dias' => [['ejercicios' => [['nombre' => 'Press', 'series' => 3]]]]];
    $plan = ['semanas' => [
        ['numero' => 1, 'fase' => 'A'] + $semana,
        ['numero' => 2, 'fase' => 'B'] + $semana,
        ['numero' => 3, 'fase' => 'C'] + $semana,
        ['numero' => 4, 'fase' => 'D'] + $semana,
    ]];
    $result = $v->check(ctx($plan, ['min_weeks' => 4]));
    expect($result)->toHaveCount(1);
});

it('UrlMatchesPattern usa delimitador ~ para evitar conflicto con /', function () {
    $v = new UrlMatchesPatternValidator($this->resolver);
    $plan = ['semanas' => [['dias' => [['ejercicios' => [
        ['gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/press.gif'],
    ]]]]]];
    $result = $v->check(ctx($plan, [
        'json_path' => '$..ejercicios[*].gif_url',
        'expected_pattern' => '^https://raw\\.githubusercontent\\.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/',
    ]));
    expect($result)->toHaveCount(0);
});

it('UrlMatchesPattern detecta URL mala', function () {
    $v = new UrlMatchesPatternValidator($this->resolver);
    $plan = ['semanas' => [['dias' => [['ejercicios' => [
        ['gif_url' => 'https://wellcorefitness.com/storage/exercises/press.gif'],
    ]]]]]];
    $result = $v->check(ctx($plan, [
        'json_path' => '$..ejercicios[*].gif_url',
        'expected_pattern' => '^https://raw\\.githubusercontent\\.com/',
    ]));
    expect($result)->toHaveCount(1);
});
