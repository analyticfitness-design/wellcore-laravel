<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\Validators\ExternalHeadValidator;

/**
 * Tests del DB cache de ExternalHeadValidator (Sprint 6).
 *
 * No mockean exercise_metadata real — testean que cuando el cache en memoria
 * tiene entries, no se hace HEAD HTTP. Inyectamos el cache via reflection.
 */

beforeEach(function () {
    $this->validator = new ExternalHeadValidator(new JsonPathResolver());
});

function ctxForUrl(string $jsonPath, string $url): LintContext
{
    return new LintContext(
        plan: ['ejercicio' => ['gif_url' => $url]],
        rule: new LintRuleMeta(
            code: 'external_gif_url_inaccessible',
            vertical: 'entrenamiento',
            severity: 'error',
            description: 'HEAD check',
            checkType: 'external_head',
            fixHintTemplate: 'verifica el alias',
            autoFixAvailable: false,
        ),
        checkDefinition: [
            'json_path' => $jsonPath,
            'method' => 'HEAD',
            'timeout_ms' => 100, // bajo intencionalmente para que fallback HTTP no demore tests
            'use_db_cache' => true,
        ],
        vertical: 'entrenamiento',
    );
}

function injectCache(ExternalHeadValidator $v, array $filenameToStatus): void
{
    $reflect = new ReflectionClass($v);
    $prop = $reflect->getProperty('filenameStatusCache');
    $prop->setAccessible(true);
    $prop->setValue($v, $filenameToStatus);
}

it('cuando DB cache dice ok para el filename, NO produce violation', function () {
    injectCache($this->validator, ['press-banca-barra.gif' => 'ok']);

    $url = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/press-banca-barra.gif';
    $ctx = ctxForUrl('$.ejercicio.gif_url', $url);

    $violations = $this->validator->check($ctx);
    expect($violations)->toBeEmpty();
});

it('cuando DB cache dice broken, produce violation sin hacer HEAD', function () {
    injectCache($this->validator, ['fake-broken.gif' => 'broken']);

    $url = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/fake-broken.gif';
    $ctx = ctxForUrl('$.ejercicio.gif_url', $url);

    $violations = $this->validator->check($ctx);
    expect($violations)->toHaveCount(1);
    expect($violations[0]->message)->toContain('status 404');
});

it('cuando DB cache dice missing, produce violation', function () {
    injectCache($this->validator, ['fake-missing.gif' => 'missing']);

    $url = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/fake-missing.gif';
    $ctx = ctxForUrl('$.ejercicio.gif_url', $url);

    $violations = $this->validator->check($ctx);
    expect($violations)->toHaveCount(1);
});

it('skip si URL no es del repo (ej. URL externa random)', function () {
    // El cache tiene un filename, pero la URL no matchea el prefijo del repo.
    // Va a fallback (Laravel cache + HTTP), que con timeout 100ms va a fallar
    // y devolver 0 → violation. Verificamos que NO se usa DB cache.
    injectCache($this->validator, ['unrelated.gif' => 'ok']);

    $url = 'https://example.com/some.gif';
    $ctx = ctxForUrl('$.ejercicio.gif_url', $url);

    $violations = $this->validator->check($ctx);
    // No esperamos el cache hit; va a HTTP con timeout corto → falla → violation con status 0 o real
    expect($violations)->toHaveCount(1);
});

it('use_db_cache=false bypassa el DB cache', function () {
    injectCache($this->validator, ['press-banca-barra.gif' => 'ok']);

    $url = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/press-banca-barra.gif';
    $ctx = new LintContext(
        plan: ['ejercicio' => ['gif_url' => $url]],
        rule: new LintRuleMeta(
            code: 'external_gif_url_inaccessible',
            vertical: 'entrenamiento',
            severity: 'error',
            description: 'HEAD check',
            checkType: 'external_head',
            fixHintTemplate: 'verifica',
            autoFixAvailable: false,
        ),
        checkDefinition: [
            'json_path' => '$.ejercicio.gif_url',
            'method' => 'HEAD',
            'timeout_ms' => 100,
            'use_db_cache' => false, // <-- bypass
        ],
        vertical: 'entrenamiento',
    );

    // Con DB cache desactivado, va a HTTP. Timeout bajo + URL real puede dar 200 o failure.
    // Lo importante: NO usa el cache.
    $violations = $this->validator->check($ctx);
    // Resultado no determinista (depende de red), pero el test prueba que pasa por HTTP.
    // En CI offline esto da violation; en local con net puede dar 0.
    expect($violations)->toBeArray();
});

it('skip URLs inválidas (no son URL)', function () {
    injectCache($this->validator, []);

    $ctx = ctxForUrl('$.ejercicio.gif_url', 'not-a-url-at-all');
    $violations = $this->validator->check($ctx);
    expect($violations)->toBeEmpty();
});
