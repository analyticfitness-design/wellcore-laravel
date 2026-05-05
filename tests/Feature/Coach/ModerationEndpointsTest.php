<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\ModerationAction;
use App\Models\PinnedPost;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

if (! function_exists('wcCoachModAuthHeader')) {
    function wcCoachModAuthHeader(Admin $admin): array
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
    $this->post = CommunityPost::factory()->create([
        'client_id' => $this->client->id,
        'coach_admin_id' => $this->coach->id,
    ]);
});

it('coach pins a post', function () {
    $this->withHeaders(wcCoachModAuthHeader($this->coach))
        ->postJson("/api/v/coach/posts/{$this->post->id}/pin", ['hours' => 24])
        ->assertOk();

    expect(PinnedPost::where('post_id', $this->post->id)->exists())->toBeTrue();
    expect(ModerationAction::where('target_id', $this->post->id)->where('action_type', 'pin')->exists())->toBeTrue();
});

it('coach cannot pin another coachs post (policy)', function () {
    $otherCoach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $otherClient = Client::factory()->create(['coach_id' => $otherCoach->id]);
    $otherPost = CommunityPost::factory()->create([
        'client_id' => $otherClient->id,
        'coach_admin_id' => $otherCoach->id,
    ]);

    $this->withHeaders(wcCoachModAuthHeader($this->coach))
        ->postJson("/api/v/coach/posts/{$otherPost->id}/pin", ['hours' => 24])
        ->assertForbidden();
});

it('coach makes post official', function () {
    $this->withHeaders(wcCoachModAuthHeader($this->coach))
        ->postJson("/api/v/coach/posts/{$this->post->id}/make-official")
        ->assertOk();

    $this->post->refresh();
    expect((bool) $this->post->is_official)->toBeTrue();
});

it('coach soft-deletes post with reason', function () {
    $this->withHeaders(wcCoachModAuthHeader($this->coach))
        ->deleteJson("/api/v/coach/posts/{$this->post->id}", ['reason' => 'spam'])
        ->assertOk();

    $this->post->refresh();
    expect((bool) $this->post->visible)->toBeFalse();
});
