<?php

declare(strict_types=1);

use App\Services\ComposeEngine\Periodization\PeriodizationApplier;

beforeEach(function () {
    $this->applier = new PeriodizationApplier();
});

it('setRepsForPhaseIsolation usa rangos para diferenciarse de compounds', function () {
    foreach (['Adaptación', 'Hipertrofia', 'Fuerza', 'Peak'] as $fase) {
        $compound = $this->applier->setRepsForPhase($fase);
        $isolation = $this->applier->setRepsForPhaseIsolation($fase);
        // Las isolations siempre usan reps con rango "X-Y" para evitar collision
        // con compounds (que usan valor fijo "X").
        expect($isolation['reps'])->toContain('-');
        expect($isolation['reps'])->not->toBe($compound['reps']);
    }
});

it('isolation Adaptación: 3×12-15 (volumen alto)', function () {
    $r = $this->applier->setRepsForPhaseIsolation('Adaptación');
    expect($r['series'])->toBe(3);
    expect($r['reps'])->toBe('12-15');
});

it('isolation Hipertrofia: 3×10-12 (volumen medio)', function () {
    $r = $this->applier->setRepsForPhaseIsolation('Hipertrofia');
    expect($r['series'])->toBe(3);
    expect($r['reps'])->toBe('10-12');
});

it('isolation Peak: 4×8-10 (reps moderadas con más series)', function () {
    $r = $this->applier->setRepsForPhaseIsolation('Peak');
    expect($r['series'])->toBe(4);
    expect($r['reps'])->toBe('8-10');
});

it('isolation principiante: limita a 3 series máx (igual que compound)', function () {
    $r = $this->applier->setRepsForPhaseIsolation('Peak', 'principiante');
    expect($r['series'])->toBe(3);
});

it('descansos isolation son MENORES que compounds (recuperación local)', function () {
    foreach (['Adaptación', 'Hipertrofia', 'Fuerza'] as $fase) {
        $compoundSec = (int) $this->applier->setRepsForPhase($fase)['descanso'];
        $isolationSec = (int) $this->applier->setRepsForPhaseIsolation($fase)['descanso'];
        expect($isolationSec)->toBeLessThanOrEqual($compoundSec);
    }
});

it('fase desconocida devuelve default razonable', function () {
    $r = $this->applier->setRepsForPhaseIsolation('Inventada');
    expect($r['series'])->toBe(3);
    expect($r['reps'])->toBe('10-12');
});

it('genera 8 combinaciones únicas en las 4 fases principales (compound + isolation)', function () {
    $combos = [];
    foreach (['Adaptación', 'Hipertrofia', 'Fuerza', 'Peak'] as $fase) {
        $c = $this->applier->setRepsForPhase($fase);
        $i = $this->applier->setRepsForPhaseIsolation($fase);
        $combos[] = $c['series'] . '×' . $c['reps'];
        $combos[] = $i['series'] . '×' . $i['reps'];
    }
    expect(array_unique($combos))->toHaveCount(8);
});
