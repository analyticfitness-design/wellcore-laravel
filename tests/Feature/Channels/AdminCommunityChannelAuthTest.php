<?php

/**
 * Channel auth: admin.community
 *
 * Auth via Bearer token (auth_tokens). Forces `reverb` broadcaster per test
 * so /broadcasting/auth runs the channel callbacks (the default `null`
 * driver short-circuits channel authorization).
 *
 * Helpers wcChannelAuthHeaderForAdmin/Client live in
 * CoachCommunityChannelAuthTest.php and are loaded by Pest before this file.
 */

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Broadcast;

uses(DatabaseTransactions::class);

beforeEach(function () {
    config(['broadcasting.default' => 'reverb']);
    config(['broadcasting.connections.reverb.key' => 'test-key']);
    config(['broadcasting.connections.reverb.secret' => 'test-secret']);
    config(['broadcasting.connections.reverb.app_id' => 'test-app']);
    app()->forgetInstance(\Illuminate\Contracts\Broadcasting\Factory::class);
    Broadcast::clearResolvedInstances();
    // Reload channel definitions into the freshly-resolved broadcaster instance.
    include base_path('routes/channels.php');
});

it('allows superadmin on admin.community channel', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Superadmin->value]);

    $this->withHeaders(wcChannelAuthHeaderForAdmin($admin))
        ->postJson('/broadcasting/auth', [
            'channel_name' => 'private-admin.community',
            'socket_id'    => '123.456',
        ])
        ->assertOk();
});

it('rejects coach from admin.community channel', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);

    $this->withHeaders(wcChannelAuthHeaderForAdmin($coach))
        ->postJson('/broadcasting/auth', [
            'channel_name' => 'private-admin.community',
            'socket_id'    => '123.456',
        ])
        ->assertForbidden();
});

it('rejects client from admin.community channel', function () {
    $client = Client::factory()->create();

    $this->withHeaders(wcChannelAuthHeaderForClient($client))
        ->postJson('/broadcasting/auth', [
            'channel_name' => 'private-admin.community',
            'socket_id'    => '123.456',
        ])
        ->assertForbidden();
});
