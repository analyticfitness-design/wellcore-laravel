<?php

declare(strict_types=1);

use App\Models\Client;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    if (! Schema::hasColumn('clients', 'locale')) {
        $this->markTestSkipped('Migración i18n no corrida en DB de test todavía.');
    }
});

it('persists locale change for authenticated client', function (): void {
    $client = Client::factory()->create([
        'locale' => 'es',
        'locale_locked' => false,
    ]);
    actingAsClient($client);

    $response = $this->patchJson('/api/v/me/locale', ['locale' => 'en']);

    $response->assertOk()
        ->assertJson(['ok' => true, 'locale' => 'en']);

    expect($client->fresh()->locale)->toBe('en');
});

it('rejects unsupported locale values', function (): void {
    $client = Client::factory()->create(['locale' => 'es', 'locale_locked' => false]);
    actingAsClient($client);

    $this->patchJson('/api/v/me/locale', ['locale' => 'fr'])
        ->assertStatus(422);

    expect($client->fresh()->locale)->toBe('es');
});

it('returns 403 when locale is locked', function (): void {
    $client = Client::factory()->create([
        'locale' => 'es',
        'locale_locked' => true,
    ]);
    actingAsClient($client);

    $response = $this->patchJson('/api/v/me/locale', ['locale' => 'en']);

    $response->assertStatus(403)
        ->assertJson(['locale_locked' => true]);

    expect($client->fresh()->locale)->toBe('es');
});

it('updates unit_system when provided', function (): void {
    $client = Client::factory()->create([
        'locale' => 'es',
        'locale_locked' => false,
        'unit_system' => 'metric',
    ]);
    actingAsClient($client);

    $this->patchJson('/api/v/me/locale', [
        'locale' => 'en',
        'unit_system' => 'imperial',
    ])->assertOk();

    $fresh = $client->fresh();
    expect($fresh->locale)->toBe('en');
    expect($fresh->unit_system)->toBe('imperial');
});

it('rejects unsupported unit_system values', function (): void {
    $client = Client::factory()->create(['locale' => 'es', 'locale_locked' => false]);
    actingAsClient($client);

    $this->patchJson('/api/v/me/locale', [
        'locale' => 'es',
        'unit_system' => 'rods-per-fortnight',
    ])->assertStatus(422);
});

it('requires authentication', function (): void {
    $this->patchJson('/api/v/me/locale', ['locale' => 'en'])
        ->assertStatus(401);
});
