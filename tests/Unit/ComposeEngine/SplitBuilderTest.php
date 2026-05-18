<?php

declare(strict_types=1);

use App\Services\ComposeEngine\Splits\SplitBuilder;

beforeEach(function () {
    $this->builder = new SplitBuilder();
});

it('body_part_split_5d devuelve 5 días', function () {
    $days = $this->builder->build('body_part_split_5d');

    expect($days)->toHaveCount(5);
    expect($days[0]->dayName)->toBe('Lunes');
    expect($days[4]->dayName)->toBe('Viernes');
});

it('body_part_split_5d con gender=F sesga énfasis a glúteo', function () {
    $days = $this->builder->build('body_part_split_5d', gender: 'F');

    expect($days[0]->groupLabel)->toContain('Glúteo');
    expect($days[4]->groupLabel)->toContain('Glúteo');
});

it('body_part_split_5d con goal=perdida_grasa también sesga a glúteo', function () {
    $days = $this->builder->build('body_part_split_5d', goal: 'perdida_grasa');

    expect($days[0]->groupLabel)->toContain('Glúteo');
});

it('body_part_split_5d con gender=M + hipertrofia da split clásico (Pecho/Espalda/Pierna)', function () {
    $days = $this->builder->build('body_part_split_5d', gender: 'M', goal: 'hipertrofia');

    expect($days[0]->groupLabel)->toContain('Pecho');
    expect($days[1]->groupLabel)->toContain('Espalda');
    expect($days[2]->groupLabel)->toContain('Pierna');
});

it('upper_lower_4d devuelve 4 días alternando upper/lower', function () {
    $days = $this->builder->build('upper_lower_4d');

    expect($days)->toHaveCount(4);
    expect($days[0]->groupLabel)->toContain('Upper');
    expect($days[1]->groupLabel)->toContain('Lower');
    expect($days[2]->groupLabel)->toContain('Upper');
    expect($days[3]->groupLabel)->toContain('Lower');
});

it('ppl_6d devuelve 6 días Push/Pull/Legs A+B', function () {
    $days = $this->builder->build('ppl_6d');

    expect($days)->toHaveCount(6);
    expect($days[0]->groupLabel)->toContain('Push');
    expect($days[1]->groupLabel)->toContain('Pull');
    expect($days[2]->groupLabel)->toContain('Legs');
    expect($days[3]->groupLabel)->toContain('Push');
    expect($days[4]->groupLabel)->toContain('Pull');
    expect($days[5]->groupLabel)->toContain('Legs');
});

it('lanza RuntimeException si methodology no tiene split definido', function () {
    expect(fn () => $this->builder->build('inexistente'))->toThrow(RuntimeException::class);
});

it('cada SplitDay tiene muscleTargets no vacío', function () {
    foreach (['body_part_split_5d', 'upper_lower_4d', 'ppl_6d'] as $slug) {
        $days = $this->builder->build($slug);
        foreach ($days as $day) {
            expect($day->muscleTargets)->not->toBeEmpty();
        }
    }
});
