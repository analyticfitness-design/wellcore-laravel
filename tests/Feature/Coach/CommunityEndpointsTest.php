<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PinnedPost;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

if (! function_exists('wcCoachAuthHeader')) {
    function wcCoachAuthHeader(Admin $admin): array
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
    $this->coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $this->client = Client::factory()->create(['coach_id' => $this->coach->id]);
});

it('returns 401 without auth on coach community posts', function () {
    $this->getJson('/api/v/coach/community/posts')->assertUnauthorized();
});

it('returns paginated posts of coachs clients', function () {
    CommunityPost::factory()->count(3)->create([
        'client_id' => $this->client->id,
        'coach_admin_id' => $this->coach->id,
    ]);

    $this->withHeaders(wcCoachAuthHeader($this->coach))
        ->getJson('/api/v/coach/community/posts')
        ->assertOk()
        ->assertJsonStructure(['data', 'current_page', 'last_page', 'total'])
        ->assertJsonPath('total', 3);
});

it('respects filter=pinned query param on coach feed', function () {
    $post = CommunityPost::factory()->create([
        'client_id' => $this->client->id,
        'coach_admin_id' => $this->coach->id,
    ]);
    PinnedPost::create([
        'post_id' => $post->id,
        'pinned_by_type' => 'coach',
        'pinned_by_id' => $this->coach->id,
        'pinned_at' => now(),
        'pinned_until' => now()->addDay(),
    ]);

    CommunityPost::factory()->create([
        'client_id' => $this->client->id,
        'coach_admin_id' => $this->coach->id,
    ]);

    $this->withHeaders(wcCoachAuthHeader($this->coach))
        ->getJson('/api/v/coach/community/posts?filter=pinned')
        ->assertOk()
        ->assertJsonPath('total', 1);
});

it('returns pulse summary with top performers and at-risk', function () {
    $this->withHeaders(wcCoachAuthHeader($this->coach))
        ->getJson('/api/v/coach/community/pulse')
        ->assertOk()
        ->assertJsonStructure([
            'team_health_score',
            'top_performers',
            'at_risk_clients',
        ]);
});
