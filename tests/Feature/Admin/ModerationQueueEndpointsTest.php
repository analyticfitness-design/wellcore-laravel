<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

if (! function_exists('wcAdminModQueueAuthHeader')) {
    function wcAdminModQueueAuthHeader(Admin $admin): array
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
    $client = Client::factory()->create();
    $this->post = CommunityPost::factory()->create(['client_id' => $client->id]);
    $this->report = PostReport::create([
        'post_id' => $this->post->id,
        'reporter_id' => $client->id,
        'reason' => 'spam',
        'status' => 'pending',
        'created_at' => now(),
    ]);
});

it('lists pending reports for admin', function () {
    $this->withHeaders(wcAdminModQueueAuthHeader($this->admin))
        ->getJson('/api/v/admin/community/moderation/queue')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('admin dismisses a report', function () {
    $this->withHeaders(wcAdminModQueueAuthHeader($this->admin))
        ->postJson("/api/v/admin/community/moderation/{$this->report->id}/dismiss")
        ->assertOk();

    $this->report->refresh();
    expect($this->report->status)->toBe('dismissed');
});
