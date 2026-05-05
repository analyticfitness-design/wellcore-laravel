<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\PersonalRecord;
use App\Services\GroupPulseAggregator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

describe('GroupPulseAggregator coach scope', function () {
    beforeEach(function () {
        $this->coachA = Admin::factory()->create();
        $this->coachB = Admin::factory()->create();
        $this->aggregator = new GroupPulseAggregator;
    });

    it('does not leak events from other coaches', function () {
        $clientA = Client::factory()->create(['coach_id' => $this->coachA->id, 'autoshare_pr' => 1]);
        $clientB = Client::factory()->create(['coach_id' => $this->coachB->id, 'autoshare_pr' => 1]);

        PersonalRecord::factory()->create([
            'client_id' => $clientA->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $clientB->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);

        $eventsA = $this->aggregator->buildFeed($this->coachA->id, 'today', 'all');
        $eventsB = $this->aggregator->buildFeed($this->coachB->id, 'today', 'all');

        expect(collect($eventsA)->where('type', 'pr'))->toHaveCount(1);
        expect(collect($eventsB)->where('type', 'pr'))->toHaveCount(1);
    });

    it('returns empty stats and feed for non-existent coach', function () {
        $stats = $this->aggregator->computeStats(999999);
        $feed = $this->aggregator->buildFeed(999999, 'today', 'all');

        expect($stats['workouts_today'])->toBe(0);
        expect($feed)->toBeEmpty();
    });
});
