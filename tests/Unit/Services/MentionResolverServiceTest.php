<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostMention;
use App\Services\MentionResolverService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->service = new MentionResolverService;
});

it('parses @cliente_X tokens from body', function () {
    $tokens = $this->service->extract('Hola @cliente_42 y también @cliente_7');
    expect($tokens)->toBe([
        ['type' => 'client', 'id' => 42],
        ['type' => 'client', 'id' => 7],
    ]);
});

it('parses @coach and @admin keywords', function () {
    $tokens = $this->service->extract('Aviso @coach y también @admin');
    expect($tokens)->toContain(['type' => 'coach', 'id' => null]);
    expect($tokens)->toContain(['type' => 'admin', 'id' => null]);
});

it('rejects malformed mentions (XSS/injection)', function () {
    $tokens = $this->service->extract('Bad @<script> @cliente_abc @cliente_-1');
    expect($tokens)->toBe([]);
});

it('persists mentions for a client post body', function () {
    $client = Client::factory()->create();
    $coach = Admin::factory()->create(['role' => 'coach']);
    $target = Client::factory()->create();

    $post = CommunityPost::factory()->create([
        'client_id' => $client->id,
        'content' => "Felicitaciones @cliente_{$target->id}",
        'coach_admin_id' => $coach->id,
    ]);

    $created = $this->service->persistForPost($post, mentionerType: 'client', mentionerId: $client->id);

    expect($created)->toBe(1);
    $row = PostMention::where('post_id', $post->id)->first();
    expect($row->mentioned_type)->toBe('client');
    expect($row->mentioned_id)->toBe($target->id);
});

it('search returns clients matching prefix scoped to coach', function () {
    $coach = Admin::factory()->create(['role' => 'coach']);
    $matching = Client::factory()->create(['name' => 'Carlos Pérez', 'coach_id' => $coach->id]);
    $other = Client::factory()->create(['name' => 'Carlos Otro Coach']);

    $results = $this->service->searchMentionTargets('Carl', scopeCoachId: $coach->id);

    expect(collect($results)->pluck('id')->all())->toContain($matching->id);
    expect(collect($results)->pluck('id')->all())->not->toContain($other->id);
});
