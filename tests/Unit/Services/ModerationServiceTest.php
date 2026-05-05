<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\ModerationAction;
use App\Models\PinnedPost;
use App\Models\PostReport;
use App\Services\ModerationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Carbon::setTestNow('2026-05-05 12:00:00');
    $this->service = new ModerationService;
    $this->coach = Admin::factory()->create(['role' => 'coach']);
    $this->client = Client::factory()->create(['coach_id' => $this->coach->id]);
    $this->post = CommunityPost::factory()->create([
        'client_id' => $this->client->id,
        'coach_admin_id' => $this->coach->id,
    ]);
});

afterEach(fn () => Carbon::setTestNow());

it('pins a post and writes audit log', function () {
    $this->service->pinPost($this->post, $this->coach, 'coach', 24, 'Felicidades!');

    $pinned = PinnedPost::where('post_id', $this->post->id)->first();
    expect($pinned)->not->toBeNull();
    expect($pinned->pinned_by_type)->toBe('coach');
    expect($pinned->pinned_by_id)->toBe($this->coach->id);
    expect($pinned->pinned_until->format('Y-m-d H:i'))->toBe(Carbon::now()->addHours(24)->format('Y-m-d H:i'));

    $audit = ModerationAction::where('target_id', $this->post->id)
        ->where('action_type', 'pin')->first();
    expect($audit)->not->toBeNull();
    expect($audit->actor_id)->toBe($this->coach->id);
});

it('unpins a post', function () {
    $this->service->pinPost($this->post, $this->coach, 'coach', 24, null);
    $this->service->unpinPost($this->post, $this->coach, 'coach');

    $active = PinnedPost::where('post_id', $this->post->id)
        ->where(fn ($q) => $q->whereNull('pinned_until')->orWhere('pinned_until', '>', now()))
        ->exists();
    expect($active)->toBeFalse();

    $audit = ModerationAction::where('target_id', $this->post->id)
        ->where('action_type', 'unpin')->first();
    expect($audit)->not->toBeNull();
});

it('soft deletes a post by setting visible=false', function () {
    $this->service->deletePost($this->post, $this->coach, 'coach', 'spam');
    $this->post->refresh();
    expect((bool) $this->post->visible)->toBeFalse();

    $audit = ModerationAction::where('target_id', $this->post->id)
        ->where('action_type', 'delete')->first();
    expect($audit->reason)->toBe('spam');
});

it('makes a post official', function () {
    $this->service->makeOfficial($this->post, $this->coach, 'coach');
    $this->post->refresh();
    expect((bool) $this->post->is_official)->toBeTrue();

    $audit = ModerationAction::where('target_id', $this->post->id)
        ->where('action_type', 'make_official')->first();
    expect($audit)->not->toBeNull();
});

it('dismisses a report and updates status', function () {
    $report = PostReport::create([
        'post_id' => $this->post->id,
        'reporter_id' => $this->client->id,
        'reason' => 'spam',
        'status' => 'pending',
    ]);

    $admin = Admin::factory()->create(['role' => 'superadmin']);
    $this->service->dismissReport($report, $admin);

    $report->refresh();
    expect($report->status)->toBe('dismissed');
    expect($report->reviewed_by_admin_id)->toBe($admin->id);
});
