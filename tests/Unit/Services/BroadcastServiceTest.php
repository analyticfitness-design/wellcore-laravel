<?php

use App\Models\Admin;
use App\Models\BroadcastMessage;
use App\Models\Client;
use App\Services\BroadcastService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->service = new BroadcastService;
    $this->admin = Admin::factory()->create(['role' => 'superadmin']);
});

it('counts segmented client recipients without sending (dry run)', function () {
    Client::factory()->count(5)->create(['plan' => 'rise', 'status' => 'activo']);
    Client::factory()->count(3)->create(['plan' => 'elite', 'status' => 'activo']);
    Client::factory()->count(2)->create(['plan' => 'rise', 'status' => 'inactivo']);

    $count = $this->service->previewRecipients(
        audience: 'clients',
        segment: ['plan' => ['rise'], 'status' => ['activo']],
    );

    expect($count)->toBe(5);
});

it('counts coach recipients', function () {
    Admin::factory()->count(4)->create(['role' => 'coach']);

    $count = $this->service->previewRecipients(audience: 'coaches', segment: []);
    expect($count)->toBeGreaterThanOrEqual(4);
});

it('records broadcast and recipients_count when dispatching to clients', function () {
    Client::factory()->count(3)->create(['plan' => 'metodo', 'status' => 'activo']);

    $broadcast = $this->service->dispatch(
        sender: $this->admin,
        senderType: 'admin',
        audience: 'clients',
        segment: ['plan' => ['metodo']],
        subject: 'Hola',
        body: 'Mensaje de prueba',
        pushEnabled: false,
    );

    expect($broadcast)->toBeInstanceOf(BroadcastMessage::class);
    expect($broadcast->recipients_count)->toBe(3);
    expect($broadcast->audience_type)->toBe('clients');
    expect($broadcast->subject)->toBe('Hola');
});

it('chunks delivery for >100 recipients', function () {
    Client::factory()->count(150)->create(['plan' => 'esencial', 'status' => 'activo']);

    $broadcast = $this->service->dispatch(
        sender: $this->admin,
        senderType: 'admin',
        audience: 'clients',
        segment: ['plan' => ['esencial']],
        subject: null,
        body: 'Bulk',
        pushEnabled: false,
    );

    expect($broadcast->recipients_count)->toBe(150);
});
