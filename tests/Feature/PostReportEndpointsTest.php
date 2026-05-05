<?php

declare(strict_types=1);

use App\Models\AuthToken;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

if (! function_exists('wcClientReportAuthHeader')) {
    function wcClientReportAuthHeader(Client $client): array
    {
        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type' => 'client',
            'user_id' => $client->id,
            'token' => $token,
            'ip_address' => '127.0.0.1',
            'expires_at' => now()->addDay(),
            'created_at' => now(),
        ]);

        return ['Authorization' => "Bearer {$token}"];
    }
}

it('client reports a post (creates report row)', function () {
    $reporter = Client::factory()->create();
    $post = CommunityPost::factory()->create();

    $this->withHeaders(wcClientReportAuthHeader($reporter))
        ->postJson("/api/v/community/posts/{$post->id}/report", [
            'reason' => 'spam',
            'reason_detail' => 'repetitive content',
        ])
        ->assertOk();

    expect(PostReport::where('post_id', $post->id)->where('reporter_id', $reporter->id)->exists())->toBeTrue();
});

it('rejects duplicate report from same reporter', function () {
    $reporter = Client::factory()->create();
    $post = CommunityPost::factory()->create();

    PostReport::create([
        'post_id' => $post->id,
        'reporter_id' => $reporter->id,
        'reason' => 'spam',
        'status' => 'pending',
        'created_at' => now(),
    ]);

    $this->withHeaders(wcClientReportAuthHeader($reporter))
        ->postJson("/api/v/community/posts/{$post->id}/report", ['reason' => 'spam'])
        ->assertStatus(409);
});
