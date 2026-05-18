<?php

declare(strict_types=1);

use App\Models\Kb\ExerciseMetadata;
use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\ExerciseGifFromV2RepoValidator;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->validator = new ExerciseGifFromV2RepoValidator(new JsonPathResolver());
    // Limpiamos cache para que cada test lea fresh de DB
    ExerciseGifFromV2RepoValidator::flushCache();
});

function ctxForV2Gif(array $plan): LintContext
{
    return new LintContext(
        plan: $plan,
        rule: new LintRuleMeta(
            code: 'hard_exercise_gif_from_v2_repo',
            vertical: 'entrenamiento',
            severity: 'error',
            description: 'gif from v2 repo only',
            checkType: 'heuristic',
            fixHintTemplate: 'reemplazar por alias canónico',
            autoFixAvailable: false,
        ),
        checkDefinition: [],
        vertical: 'entrenamiento',
    );
}

function buildEntrenamientoPlan(array $ejercicios): array
{
    return [
        'plan_type' => 'entrenamiento',
        'semanas' => [
            [
                'numero' => 1,
                'fase' => 'test',
                'dias' => [
                    [
                        'dia_semana' => 'Lunes',
                        'grupo_muscular' => 'gluteos',
                        'ejercicios' => $ejercicios,
                    ],
                ],
            ],
        ],
    ];
}

it('skip si plan_type != entrenamiento', function () {
    $plan = ['plan_type' => 'nutricion'];
    expect($this->validator->check(ctxForV2Gif($plan)))->toBeEmpty();
});

it('rechaza ejercicio con gif_url fuera del repo v2', function () {
    $ej = [
        'nombre' => 'Ejercicio inventado',
        'series' => 3, 'repeticiones' => '10', 'descanso' => '60s', 'rir' => 2,
        'gif_url' => 'https://imgur.com/fake.gif',
    ];
    $violations = $this->validator->check(ctxForV2Gif(buildEntrenamientoPlan([$ej])));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->severity)->toBe('error');
    expect($violations[0]->message)->toContain('fuera del repo oficial');
});

it('rechaza ejercicio sin gif_url', function () {
    $ej = ['nombre' => 'Sin gif', 'series' => 3, 'repeticiones' => '10', 'descanso' => '60s', 'rir' => 2];
    $violations = $this->validator->check(ctxForV2Gif(buildEntrenamientoPlan([$ej])));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('sin gif_url');
});

it('rechaza filename del repo v2 que NO está en exercise_metadata', function () {
    $ej = [
        'nombre' => 'Ejercicio fantasma',
        'series' => 3, 'repeticiones' => '10', 'descanso' => '60s', 'rir' => 2,
        'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/ejercicio-que-no-existe-en-bd.gif',
    ];
    $violations = $this->validator->check(ctxForV2Gif(buildEntrenamientoPlan([$ej])));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('NO está en wellcore_kb.exercise_metadata');
});

it('acepta ejercicio canónico (filename existe en exercise_metadata)', function () {
    // Tomamos un alias real del catálogo limpio
    $real = ExerciseMetadata::query()
        ->whereNotNull('gif_filename')
        ->where('gif_filename', '!=', '')
        ->first();

    if ($real === null) {
        test()->markTestSkipped('exercise_metadata vacío en este entorno.');
    }

    $ej = [
        'nombre' => $real->alias,
        'series' => 3, 'repeticiones' => '10', 'descanso' => '60s', 'rir' => 2,
        'gif_url' => ExerciseMetadata::GIF_REPO_BASE_URL . $real->gif_filename,
    ];
    expect($this->validator->check(ctxForV2Gif(buildEntrenamientoPlan([$ej]))))->toBeEmpty();
});

it('rechaza path inválido tras el repo base (sin .gif)', function () {
    $ej = [
        'nombre' => 'Sin extension',
        'series' => 3, 'repeticiones' => '10', 'descanso' => '60s', 'rir' => 2,
        'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/no-tiene-extension',
    ];
    $violations = $this->validator->check(ctxForV2Gif(buildEntrenamientoPlan([$ej])));
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('Debe terminar en .gif');
});

it('reporta TODOS los ejercicios inválidos del plan, no solo el primero', function () {
    $ej1 = ['nombre' => 'Falso 1', 'series' => 3, 'repeticiones' => '10', 'descanso' => '60s', 'rir' => 2, 'gif_url' => 'https://other.com/a.gif'];
    $ej2 = ['nombre' => 'Falso 2', 'series' => 3, 'repeticiones' => '10', 'descanso' => '60s', 'rir' => 2, 'gif_url' => 'https://other.com/b.gif'];
    $ej3 = ['nombre' => 'Falso 3', 'series' => 3, 'repeticiones' => '10', 'descanso' => '60s', 'rir' => 2];
    $violations = $this->validator->check(ctxForV2Gif(buildEntrenamientoPlan([$ej1, $ej2, $ej3])));
    expect($violations)->toHaveCount(3);
});
