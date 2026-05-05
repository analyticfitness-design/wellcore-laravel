<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\BroadcastMessage;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

if (! function_exists('wcAdminBroadcastAuthHeader')) {
    function wcAdminBroadcastAuthHeader(Admin $admin): array
    {
        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type' => 'admin',
            'user_id' => $admin->id,
            'token' => $token,
            'ip_address' => '127.0.0.1',
            'expires_at' => now()->addDay(),
            'created_at' => now(),
        ]);

        return ['Authorization' => "Bearer {$token}"];
    }
}

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => UserRole::Superadmin->value]);
});

it('returns recipient count preview without sending', function () {
    Client::factory()->count(4)->create(['plan' => 'metodo', 'status' => 'activo']);

    $this->withHeaders(wcAdminBroadcastAuthHeader($this->admin))
        ->postJson('/api/v/admin/broadcast/preview', [
            'audience' => 'clients',
            'segment' => ['plan' => ['metodo'], 'status' => ['activo']],
        ])
        ->assertOk()
        ->assertJsonPath('count', 4);
});

it('sends broadcast and persists row', function () {
    Client::factory()->count(2)->create(['plan' => 'rise', 'status' => 'activo']);
    $countBefore = BroadcastMessage::count();

    $this->withHeaders(wcAdminBroadcastAuthHeader($this->admin))
        ->postJson('/api/v/admin/broadcast/send', [
            'audience' => 'clients',
            'segment' => ['plan' => ['rise']],
            'subject' => 'Hola',
            'body' => 'Mensaje test',
            'push_enabled' => false,
        ])
        ->assertOk()
        ->assertJsonStructure(['broadcast_id', 'recipients_count']);

    expect(BroadcastMessage::count())->toBe($countBefore + 1);
});

it('rejects coach from broadcast endpoints', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);

    $this->withHeaders(wcAdminBroadcastAuthHeader($coach))
        ->postJson('/api/v/admin/broadcast/send', [
            'audience' => 'clients', 'segment' => [], 'body' => 'x',
        ])
        ->assertForbidden();
});
