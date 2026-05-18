<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    Cache::flush();
});

it('serves the spanish translations bundle', function (): void {
    $response = $this->getJson('/api/v/translations/es');

    $response->assertOk()
        ->assertJsonStructure(['locale', 'messages', 'version']);

    expect($response->json('locale'))->toBe('es');
    expect($response->json('messages.validation'))->toBeArray();
    expect($response->json('version'))->toBeString()->not->toBe('empty');
});

it('serves the english translations bundle', function (): void {
    $response = $this->getJson('/api/v/translations/en');

    $response->assertOk();
    expect($response->json('locale'))->toBe('en');
    expect($response->json('messages.validation.required'))
        ->toContain('required');
});

it('returns 404 for unsupported locales', function (): void {
    $this->getJson('/api/v/translations/pt')->assertStatus(404);
    $this->getJson('/api/v/translations/zz')->assertStatus(404);
});

it('supports etag revalidation with 304', function (): void {
    $first = $this->getJson('/api/v/translations/es');
    $first->assertOk();

    $etag = $first->headers->get('ETag');
    expect($etag)->not->toBeNull();

    $second = $this->withHeaders(['If-None-Match' => $etag])
        ->getJson('/api/v/translations/es');

    $second->assertStatus(304);
});

it('rejects locale param that does not match validation regex', function (): void {
    // Route constraint is [a-z]{2} — uppercase or longer should miss the route.
    $this->getJson('/api/v/translations/ES')->assertStatus(404);
    $this->getJson('/api/v/translations/spa')->assertStatus(404);
});
