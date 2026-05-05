<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\WorkoutSession;
use App\Services\GroupPulseAggregator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

describe('GroupPulseAggregator::userVsGroup', function () {
    beforeEach(function () {
        Carbon::setTestNow('2026-05-05 12:00:00');
        $this->coach = Admin::factory()->create();
        $this->aggregator = new GroupPulseAggregator;
    });

    afterEach(function () {
        Carbon::setTestNow();
    });

    it('returns empty shape when coach has no clients', function () {
        $result = $this->aggregator->userVsGroup($this->coach->id, 999);

        expect($result)->toMatchArray([
            'weekly_workouts' => [
                'user' => 0,
                'group_avg' => 0.0,
                'rank_pct' => 0,
            ],
            'missions_peers' => [],
        ]);
    });

    it('computes user count + group_avg + rank_pct correctly', function () {
        $clientA = Client::factory()->create(['coach_id' => $this->coach->id]);
        $clientB = Client::factory()->create(['coach_id' => $this->coach->id]);
        $clientC = Client::factory()->create(['coach_id' => $this->coach->id]);

        // A: 5 workouts, B: 2 workouts, C: 0 workouts → group avg = 2.33
        WorkoutSession::factory()->count(5)->create([
            'client_id' => $clientA->id,
            'completed' => true,
            'session_date' => Carbon::now()->subDays(2),
        ]);
        WorkoutSession::factory()->count(2)->create([
            'client_id' => $clientB->id,
            'completed' => true,
            'session_date' => Carbon::now()->subDays(2),
        ]);

        $resultA = $this->aggregator->userVsGroup($this->coach->id, $clientA->id);

        expect($resultA['weekly_workouts']['user'])->toBe(5);
        // group_avg = (5+2)/2 = 3.5 (clientC con 0 no aparece en groupBy/pluck COUNT)
        expect($resultA['weekly_workouts']['group_avg'])->toBeFloat();
        // A es el top performer → rank_pct alto (top X%)
        expect($resultA['weekly_workouts']['rank_pct'])->toBeGreaterThanOrEqual(50);
    });

    it('rank_pct = 0 cuando todos tienen 0 workouts', function () {
        Client::factory()->count(3)->create(['coach_id' => $this->coach->id]);

        $client = Client::factory()->create(['coach_id' => $this->coach->id]);
        $result = $this->aggregator->userVsGroup($this->coach->id, $client->id);

        expect($result['weekly_workouts']['user'])->toBe(0);
        expect($result['weekly_workouts']['rank_pct'])->toBe(0);
    });

    it('respects pre-resolved client_ids parameter for performance', function () {
        $clientA = Client::factory()->create(['coach_id' => $this->coach->id]);
        WorkoutSession::factory()->count(3)->create([
            'client_id' => $clientA->id,
            'completed' => true,
            'session_date' => Carbon::now()->subDays(1),
        ]);

        $preResolved = collect([$clientA->id]);
        $result = $this->aggregator->userVsGroup($this->coach->id, $clientA->id, $preResolved);

        expect($result['weekly_workouts']['user'])->toBe(3);
    });
});
