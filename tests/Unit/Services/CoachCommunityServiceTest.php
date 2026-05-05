<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PinnedPost;
use App\Models\WorkoutSession;
use App\Services\CoachCommunityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Carbon::setTestNow('2026-05-05 12:00:00');
    $this->service = new CoachCommunityService;
    $this->coach = Admin::factory()->create(['role' => 'coach']);
});

afterEach(fn () => Carbon::setTestNow());

it('returns posts only from coachs clients', function () {
    $myClient = Client::factory()->create(['coach_id' => $this->coach->id]);
    $otherClient = Client::factory()->create();

    $myPost = CommunityPost::factory()->create([
        'client_id' => $myClient->id,
        'coach_admin_id' => $this->coach->id,
    ]);
    $otherPost = CommunityPost::factory()->create(['client_id' => $otherClient->id]);

    $feed = $this->service->getFeed($this->coach->id, filter: 'all', perPage: 50);

    $ids = collect($feed['data'])->pluck('id')->all();
    expect($ids)->toContain($myPost->id);
    expect($ids)->not->toContain($otherPost->id);
});

it('filters by pinned posts', function () {
    $client = Client::factory()->create(['coach_id' => $this->coach->id]);
    $pinned = CommunityPost::factory()->create([
        'client_id' => $client->id,
        'coach_admin_id' => $this->coach->id,
    ]);
    $normal = CommunityPost::factory()->create([
        'client_id' => $client->id,
        'coach_admin_id' => $this->coach->id,
    ]);

    PinnedPost::create([
        'post_id' => $pinned->id,
        'pinned_by_type' => 'coach',
        'pinned_by_id' => $this->coach->id,
        'pinned_at' => Carbon::now(),
        'pinned_until' => Carbon::now()->addDay(),
    ]);

    $feed = $this->service->getFeed($this->coach->id, filter: 'pinned', perPage: 50);
    $ids = collect($feed['data'])->pluck('id')->all();

    expect($ids)->toContain($pinned->id);
    expect($ids)->not->toContain($normal->id);
});

it('returns top performers ordered by workouts in last 7d', function () {
    $a = Client::factory()->create(['coach_id' => $this->coach->id, 'name' => 'Alpha']);
    $b = Client::factory()->create(['coach_id' => $this->coach->id, 'name' => 'Bravo']);

    WorkoutSession::factory()->count(5)->create([
        'client_id' => $a->id,
        'completed' => true,
        'session_date' => Carbon::today(),
    ]);
    WorkoutSession::factory()->count(2)->create([
        'client_id' => $b->id,
        'completed' => true,
        'session_date' => Carbon::today(),
    ]);

    $top = $this->service->topPerformers($this->coach->id, days: 7, limit: 3);

    expect($top[0]['client_id'])->toBe($a->id);
    expect($top[0]['workout_count'])->toBe(5);
});

it('flags at-risk clients with 0 workouts in last 5 days', function () {
    $silent = Client::factory()->create(['coach_id' => $this->coach->id, 'name' => 'Silent']);
    $active = Client::factory()->create(['coach_id' => $this->coach->id, 'name' => 'Active']);

    WorkoutSession::factory()->create([
        'client_id' => $active->id,
        'completed' => true,
        'session_date' => Carbon::today(),
    ]);

    $atRisk = $this->service->atRiskClients($this->coach->id, days: 5);
    $ids = collect($atRisk)->pluck('id')->all();

    expect($ids)->toContain($silent->id);
    expect($ids)->not->toContain($active->id);
});
