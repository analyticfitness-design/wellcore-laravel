<?php

/**
 * Channel auth: coach.{coachId}.community
 *
 * Auth via Bearer token (auth_tokens) since EnsureAuthenticated middleware
 * is fundamentally token-based and does NOT respect Laravel's actingAs()
 * helper. The default test broadcaster (`null`) short-circuits channel
 * authorization, so we force `reverb` (Pusher protocol) per test to ensure
 * the channel callback in routes/channels.php is actually evaluated.
 */

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Broadcast;

uses(DatabaseTransactions::class);

beforeEach(function () {
    // Force Reverb (Pusher protocol) so /broadcasting/auth runs the channel
    // callbacks. The default `null` driver short-circuits authorization and
    // would return 200 for everything, masking authorization bugs.
    config(['broadcasting.default' => 'reverb']);
    config(['broadcasting.connections.reverb.key' => 'test-key']);
    config(['broadcasting.connections.reverb.secret' => 'test-secret']);
    config(['broadcasting.connections.reverb.app_id' => 'test-app']);
    app()->forgetInstance(\Illuminate\Contracts\Broadcasting\Factory::class);
    Broadcast::clearResolvedInstances();
    // The new Pusher broadcaster instance is empty — re-register channels.
    // The new Pusher broadcaster instance has no channels registered — reload them.
    // include (not require_once) so each test re-evaluates against the new broadcaster.
    include base_path('routes/channels.php');
});

if (! function_exists('wcChannelAuthHeaderForAdmin')) {
    function wcChannelAuthHeaderForAdmin(Admin $admin): array
    {
        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'admin',
            'user_id'    => $admin->id,
            'token'      => $token,
            'ip_address' => '127.0.0.1',
            'expires_at' => now()->addDay(),
            'created_at' => now(),
        ]);
        return ['Authorization' => "Bearer {$token}"];
    }
}

if (! function_exists('wcChannelAuthHeaderForClient')) {
    function wcChannelAuthHeaderForClient(Client $client): array
    {
        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'ip_address' => '127.0.0.1',
            'expires_at' => now()->addDay(),
            'created_at' => now(),
        ]);
        return ['Authorization' => "Bearer {$token}"];
    }
}

it('allows coach to subscribe to their own community channel', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);

    $this->withHeaders(wcChannelAuthHeaderForAdmin($coach))
        ->postJson('/broadcasting/auth', [
            'channel_name' => "private-coach.{$coach->id}.community",
            'socket_id'    => '123.456',
        ])
        ->assertOk();
});

it('rejects coach from subscribing to another coachs channel', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $other = Admin::factory()->create(['role' => UserRole::Coach->value]);

    $this->withHeaders(wcChannelAuthHeaderForAdmin($coach))
        ->postJson('/broadcasting/auth', [
            'channel_name' => "private-coach.{$other->id}.community",
            'socket_id'    => '123.456',
        ])
        ->assertForbidden();
});

it('rejects clients from coach community channel', function () {
    $coach  = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $client = Client::factory()->create();

    $this->withHeaders(wcChannelAuthHeaderForClient($client))
        ->postJson('/broadcasting/auth', [
            'channel_name' => "private-coach.{$coach->id}.community",
            'socket_id'    => '123.456',
        ])
        ->assertForbidden();
});
