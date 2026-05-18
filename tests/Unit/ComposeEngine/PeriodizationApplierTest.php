<?php

declare(strict_types=1);

use App\Services\ComposeEngine\Periodization\PeriodizationApplier;

beforeEach(function () {
    $this->applier = new PeriodizationApplier();
});

it('expande pattern de 4 semanas (1 semana por fase)', function () {
    $pattern = [
        ['weeks' => 1, 'fase' => 'Adaptación', 'rir_objetivo' => 3, 'volumen_pct' => 70],
        ['weeks' => 1, 'fase' => 'Hipertrofia', 'rir_objetivo' => 2, 'volumen_pct' => 100],
        ['weeks' => 1, 'fase' => 'Fuerza', 'rir_objetivo' => 1, 'volumen_pct' => 90],
        ['weeks' => 1, 'fase' => 'Peak', 'rir_objetivo' => 0, 'volumen_pct' => 75],
    ];

    $weeks = $this->applier->expand($pattern, 4);

    expect($weeks)->toHaveCount(4);
    expect($weeks[0]['fase'])->toBe('Adaptación');
    expect($weeks[0]['rir'])->toBe(3);
    expect($weeks[3]['fase'])->toBe('Peak');
    expect($weeks[3]['rir'])->toBe(0);
});

it('expande pattern con bloques multi-semana (2+2+1+1)', function () {
    $pattern = [
        ['weeks' => 2, 'fase' => 'Adaptación', 'rir_objetivo' => 3, 'volumen_pct' => 70],
        ['weeks' => 2, 'fase' => 'Hipertrofia', 'rir_objetivo' => 2, 'volumen_pct' => 100],
        ['weeks' => 1, 'fase' => 'Fuerza', 'rir_objetivo' => 1, 'volumen_pct' => 90],
        ['weeks' => 1, 'fase' => 'Peak', 'rir_objetivo' => 0, 'volumen_pct' => 75],
    ];

    $weeks = $this->applier->expand($pattern, 6);

    expect($weeks)->toHaveCount(6);
    expect($weeks[0]['fase'])->toBe('Adaptación');
    expect($weeks[1]['fase'])->toBe('Adaptación');
    expect($weeks[2]['fase'])->toBe('Hipertrofia');
    expect($weeks[3]['fase'])->toBe('Hipertrofia');
    expect($weeks[4]['fase'])->toBe('Fuerza');
    expect($weeks[5]['fase'])->toBe('Peak');
});

it('rellena con la última fase si pattern es más corto que duracion', function () {
    $pattern = [
        ['weeks' => 1, 'fase' => 'Adaptación', 'rir_objetivo' => 3, 'volumen_pct' => 70],
        ['weeks' => 1, 'fase' => 'Hipertrofia', 'rir_objetivo' => 2, 'volumen_pct' => 100],
    ];

    $weeks = $this->applier->expand($pattern, 4);

    expect($weeks)->toHaveCount(4);
    expect($weeks[2]['fase'])->toBe('Hipertrofia');
    expect($weeks[3]['fase'])->toBe('Hipertrofia');
});

it('trunca si pattern es más largo que duracion', function () {
    $pattern = [
        ['weeks' => 4, 'fase' => 'Hipertrofia', 'rir_objetivo' => 2, 'volumen_pct' => 100],
        ['weeks' => 4, 'fase' => 'Peak', 'rir_objetivo' => 0, 'volumen_pct' => 75],
    ];

    $weeks = $this->applier->expand($pattern, 3);

    expect($weeks)->toHaveCount(3);
    expect($weeks[2]['fase'])->toBe('Hipertrofia');
});

it('setRepsForPhase devuelve series/reps coherentes con la fase', function () {
    expect($this->applier->setRepsForPhase('Adaptación')['series'])->toBe(3);
    expect($this->applier->setRepsForPhase('Hipertrofia')['series'])->toBe(4);
    expect($this->applier->setRepsForPhase('Peak')['series'])->toBe(5);
    expect($this->applier->setRepsForPhase('Deload')['series'])->toBe(2);
});

it('setRepsForPhase limita a 3 series para principiante en fases pesadas', function () {
    expect($this->applier->setRepsForPhase('Peak', 'principiante')['series'])->toBe(3);
    expect($this->applier->setRepsForPhase('Hipertrofia', 'principiante')['series'])->toBe(3);
});

it('setRepsForPhase para fase desconocida devuelve default seguro', function () {
    $r = $this->applier->setRepsForPhase('FaseInventada');
    expect($r['series'])->toBe(3);
    expect($r['reps'])->toBe('10');
});
