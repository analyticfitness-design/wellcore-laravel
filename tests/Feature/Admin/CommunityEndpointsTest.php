<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use App\Models\CommunityPost;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

if (! function_exists('wcAdminCommunityAuthHeader')) {
    function wcAdminCommunityAuthHeader(Admin $admin): array
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

it('rejects coaches from admin community endpoints', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);

    $this->withHeaders(wcAdminCommunityAuthHeader($coach))
        ->getJson('/api/v/admin/community/pulse-cross-coach')
        ->assertForbidden();
});

it('returns coach metrics for superadmin', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $client = Client::factory()->create(['coach_id' => $coach->id]);
    CommunityPost::factory()->count(2)->create([
        'client_id' => $client->id,
        'coach_admin_id' => $coach->id,
    ]);

    $this->withHeaders(wcAdminCommunityAuthHeader($this->admin))
        ->getJson('/api/v/admin/community/pulse-cross-coach?period=week')
        ->assertOk()
        ->assertJsonStructure([
            'coaches' => [['coach_id', 'coach_name', 'posts_count', 'reactions_count', 'engagement_rate']],
            'time_series',
            'moderation_queue_count',
        ]);
});
