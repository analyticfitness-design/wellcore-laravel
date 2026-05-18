<?php

declare(strict_types=1);

use App\Services\LintEngine\JsonPath\PathMutator;

beforeEach(function () {
    $this->mutator = new PathMutator();
});

it('setAtPath reemplaza valor en root', function () {
    $doc = ['a' => 1, 'b' => 2];
    $result = $this->mutator->setAtPath($doc, '$.a', 99);
    expect($result)->toBe(['a' => 99, 'b' => 2]);
});

it('setAtPath crea path anidado', function () {
    $doc = ['comidas' => ['macros' => ['proteina_g' => 30]]];
    $result = $this->mutator->setAtPath($doc, '$.comidas.macros.proteina', 40);
    expect($result['comidas']['macros']['proteina'])->toBe(40);
    // Preserva los demás
    expect($result['comidas']['macros']['proteina_g'])->toBe(30);
});

it('setAtPath setea índice de array', function () {
    $doc = ['semanas' => [['fase' => 'A'], ['fase' => 'B']]];
    $result = $this->mutator->setAtPath($doc, '$.semanas[1].fase', 'PEAK');
    expect($result['semanas'][1]['fase'])->toBe('PEAK');
    expect($result['semanas'][0]['fase'])->toBe('A');
});

it('setAtPath NO muta el documento original', function () {
    $doc = ['a' => 1];
    $this->mutator->setAtPath($doc, '$.a', 99);
    expect($doc)->toBe(['a' => 1]);
});

it('getAtPath retorna null si path no existe', function () {
    $doc = ['a' => 1];
    expect($this->mutator->getAtPath($doc, '$.nonexistent'))->toBeNull();
});

it('getAtPath retorna valor en path anidado', function () {
    $doc = ['a' => ['b' => ['c' => 42]]];
    expect($this->mutator->getAtPath($doc, '$.a.b.c'))->toBe(42);
});

it('deleteAtPath remueve key', function () {
    $doc = ['a' => 1, 'b' => 2];
    $result = $this->mutator->deleteAtPath($doc, '$.a');
    expect($result)->toBe(['b' => 2]);
});

it('deleteAtPath remueve key anidada sin tocar el resto', function () {
    $doc = ['a' => ['x' => 1, 'y' => 2]];
    $result = $this->mutator->deleteAtPath($doc, '$.a.x');
    expect($result)->toBe(['a' => ['y' => 2]]);
});
