<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\PersonalRecord;
use App\Models\WorkoutSession;
use App\Services\GroupPulseAggregator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

describe('GroupPulseAggregator::computeStats', function () {
    beforeEach(function () {
        $this->coach = Admin::factory()->create();
        $this->aggregator = new GroupPulseAggregator;
    });

    it('counts workouts completed today by clients of the coach', function () {
        $client = Client::factory()->create(['coach_id' => $this->coach->id]);
        WorkoutSession::factory()->count(3)->create([
            'client_id' => $client->id,
            'completed' => true,
            'session_date' => Carbon::today(),
        ]);
        // Workout from yesterday should NOT count
        WorkoutSession::factory()->create([
            'client_id' => $client->id,
            'completed' => true,
            'session_date' => Carbon::yesterday(),
        ]);

        $stats = $this->aggregator->computeStats($this->coach->id);

        expect($stats['workouts_today'])->toBe(3);
    });

    it('counts personal records this week', function () {
        $client = Client::factory()->create(['coach_id' => $this->coach->id]);
        PersonalRecord::factory()->count(5)->create([
            'client_id' => $client->id,
            'is_current' => 1,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        $stats = $this->aggregator->computeStats($this->coach->id);

        expect($stats['prs_week'])->toBe(5);
    });

    it('returns zero counts when coach has no clients', function () {
        $stats = $this->aggregator->computeStats($this->coach->id);

        expect($stats)->toMatchArray([
            'workouts_today' => 0,
            'prs_week' => 0,
            'achievements_today' => 0,
            'checkins_week' => 0,
        ]);
    });
});
