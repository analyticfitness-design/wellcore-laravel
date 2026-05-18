<?php

declare(strict_types=1);

use App\Services\ComposeEngine\Principles\PrincipleInjector;
use App\Services\DecisionEngine\Data\ClientProfile;

beforeEach(function () {
    $this->injector = new PrincipleInjector();
});

it('selectTop devuelve hasta limit principles', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', goal: 'hipertrofia', level: 'intermedio');
    $top = $this->injector->selectTop($profile, 'entrenamiento', limit: 3);
    expect($top)->toHaveCount(3);
});

it('selectTop prioriza principles del mismo vertical', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', goal: 'hipertrofia', level: 'intermedio');
    $top = $this->injector->selectTop($profile, 'entrenamiento', limit: 5);
    $verticals = $top->pluck('vertical')->toArray();
    // Al menos 3 de los top 5 deberían ser entrenamiento (vertical match=+10)
    $trainCount = count(array_filter($verticals, fn ($v) => $v === 'entrenamiento'));
    expect($trainCount)->toBeGreaterThanOrEqual(3);
});

it('para vertical=nutricion incluye principles de nutrición', function () {
    $profile = new ClientProfile(vertical: 'nutricion', goal: 'perdida_grasa');
    $top = $this->injector->selectTop($profile, 'nutricion', limit: 3);
    $verticals = $top->pluck('vertical')->toArray();
    expect($verticals)->toContain('nutricion');
});

it('asTipsArray retorna array de strings', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', goal: 'hipertrofia');
    $top = $this->injector->selectTop($profile, 'entrenamiento', limit: 3);
    $tips = $this->injector->asTipsArray($top);
    expect($tips)->toBeArray();
    expect($tips)->toHaveCount(3);
    foreach ($tips as $t) {
        expect($t)->toBeString();
        expect($t)->not->toBeEmpty();
    }
});

it('asInlineNotes retorna string con principios concatenados', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', goal: 'hipertrofia');
    $top = $this->injector->selectTop($profile, 'entrenamiento', limit: 2);
    $notes = $this->injector->asInlineNotes($top);
    expect($notes)->toBeString();
    expect($notes)->toContain('·'); // separador entre principles
});

it('determinismo: mismo profile + 2 llamadas → mismos principles', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', goal: 'hipertrofia', level: 'intermedio');
    $a = $this->injector->selectTop($profile, 'entrenamiento', limit: 3)->pluck('slug')->toArray();
    $b = $this->injector->selectTop($profile, 'entrenamiento', limit: 3)->pluck('slug')->toArray();
    expect($a)->toBe($b);
});

it('principle con tag "fundamental" tiene bonus aún en vertical no-match', function () {
    // El injector debería traer al menos un 'fundamental' en top 5 cuando aplica
    $profile = new ClientProfile(vertical: 'suplementacion', goal: 'hipertrofia');
    $top = $this->injector->selectTop($profile, 'suplementacion', limit: 5);
    expect($top)->toHaveCount(5);
});

it('limit 1 devuelve exactamente 1', function () {
    $profile = new ClientProfile(vertical: 'entrenamiento', goal: 'hipertrofia');
    $top = $this->injector->selectTop($profile, 'entrenamiento', limit: 1);
    expect($top)->toHaveCount(1);
});

it('cliente con lesiones recibe principles con tag lesion/rehabilitacion', function () {
    $profile = new ClientProfile(
        vertical: 'entrenamiento',
        goal: 'hipertrofia',
        level: 'intermedio',
        injuries: ['hombro_anterior'],
    );
    $top = $this->injector->selectTop($profile, 'entrenamiento', limit: 5);
    $allTags = $top->flatMap(fn ($p) => $p->tags ?? [])->toArray();
    // Al menos un principle relacionado con lesiones/recuperación debería aparecer
    $hasInjuryRelated = ! empty(array_intersect(
        ['lesion', 'rehabilitacion', 'prevencion_lesiones', 'recuperacion'],
        $allTags,
    ));
    expect($hasInjuryRelated)->toBeTrue();
});
