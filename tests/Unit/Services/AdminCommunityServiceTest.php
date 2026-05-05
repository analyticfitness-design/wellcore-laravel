<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Services\AdminCommunityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Carbon::setTestNow('2026-05-05 12:00:00');
    $this->service = new AdminCommunityService;
});

afterEach(fn () => Carbon::setTestNow());

it('returns metrics per coach', function () {
    $coachA = Admin::factory()->create(['role' => 'coach']);
    $coachB = Admin::factory()->create(['role' => 'coach']);
    $client = Client::factory()->create(['coach_id' => $coachA->id]);

    CommunityPost::factory()->count(3)->create([
        'client_id' => $client->id,
        'coach_admin_id' => $coachA->id,
        'created_at' => Carbon::now()->subDays(2),
    ]);

    $metrics = $this->service->coachMetrics(period: 'week');

    $a = collect($metrics)->firstWhere('coach_id', $coachA->id);
    $b = collect($metrics)->firstWhere('coach_id', $coachB->id);

    expect($a['posts_count'])->toBeGreaterThanOrEqual(3);
    expect($b['posts_count'])->toBe(0);
});

it('time series of posts/day', function () {
    $coach = Admin::factory()->create(['role' => 'coach']);
    $client = Client::factory()->create(['coach_id' => $coach->id]);

    CommunityPost::factory()->create([
        'client_id' => $client->id,
        'coach_admin_id' => $coach->id,
        'created_at' => Carbon::now()->subDay(),
    ]);
    CommunityPost::factory()->create([
        'client_id' => $client->id,
        'coach_admin_id' => $coach->id,
        'created_at' => Carbon::now()->subDays(2),
    ]);

    $series = $this->service->postsTimeSeries(days: 7);

    expect($series)->toBeArray();
    expect(count($series))->toBe(7);
    foreach ($series as $point) {
        expect($point)->toHaveKey('date');
        expect($point)->toHaveKey('count');
    }
});
