<?php

declare(strict_types=1);

use App\Services\LintEngine\JsonPath\JsonPathResolver;

beforeEach(function () {
    $this->resolver = new JsonPathResolver();
});

it('resuelve key simple del root', function () {
    $doc = ['objetivo' => 'Pérdida de grasa'];
    $matches = $this->resolver->resolve($doc, '$.objetivo');
    expect($matches)->toHaveCount(1);
    expect($matches[0]->value)->toBe('Pérdida de grasa');
    expect($matches[0]->path)->toBe('$.objetivo');
});

it('retorna array vacío si key no existe', function () {
    $doc = ['objetivo' => 'x'];
    $matches = $this->resolver->resolve($doc, '$.split');
    expect($matches)->toHaveCount(0);
});

it('resuelve path anidado a.b.c', function () {
    $doc = ['comidas' => ['macros' => ['proteina' => 30]]];
    $matches = $this->resolver->resolve($doc, '$.comidas.macros.proteina');
    expect($matches)->toHaveCount(1);
    expect($matches[0]->value)->toBe(30);
});

it('resuelve wildcard [*] sobre array', function () {
    $doc = ['semanas' => [['fase' => 'A'], ['fase' => 'B']]];
    $matches = $this->resolver->resolve($doc, '$.semanas[*].fase');
    expect($matches)->toHaveCount(2);
    expect($matches[0]->value)->toBe('A');
    expect($matches[1]->value)->toBe('B');
    expect($matches[0]->path)->toBe('$.semanas[0].fase');
});

it('resuelve wildcards chained', function () {
    $doc = [
        'semanas' => [
            ['dias' => [['nombre' => 'Lun'], ['nombre' => 'Mar']]],
            ['dias' => [['nombre' => 'Lun2']]],
        ],
    ];
    $matches = $this->resolver->resolve($doc, '$.semanas[*].dias[*].nombre');
    expect($matches)->toHaveCount(3);
    expect(array_map(fn ($m) => $m->value, $matches))->toBe(['Lun', 'Mar', 'Lun2']);
});

it('resuelve descendant $..key encuentra en cualquier nivel', function () {
    $doc = [
        'semanas' => [
            ['dias' => [['ejercicios' => [['gif_url' => 'a.gif']]]]],
            ['ejercicios' => [['gif_url' => 'b.gif']]],
        ],
    ];
    $matches = $this->resolver->resolve($doc, '$..ejercicios[*].gif_url');
    expect($matches)->toHaveCount(2);
    expect(array_map(fn ($m) => $m->value, $matches))->toBe(['a.gif', 'b.gif']);
});

it('captura parent y key del match', function () {
    $doc = ['semanas' => [['fase' => 'Peak']]];
    $matches = $this->resolver->resolve($doc, '$.semanas[*].fase');
    expect($matches[0]->key)->toBe('fase');
    expect($matches[0]->parent)->toBe(['fase' => 'Peak']);
});

it('retorna 0 matches sobre path en valor no-array', function () {
    $doc = ['objetivo' => 'string-no-array'];
    $matches = $this->resolver->resolve($doc, '$.objetivo.subkey');
    expect($matches)->toHaveCount(0);
});
