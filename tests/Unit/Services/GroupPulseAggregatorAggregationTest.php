<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\WorkoutSession;
use App\Services\GroupPulseAggregator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

describe('GroupPulseAggregator workout aggregation', function () {
    beforeEach(function () {
        $this->coach = Admin::factory()->create();
        $this->aggregator = new GroupPulseAggregator;
    });

    it('aggregates when more than 5 workouts in last hour', function () {
        $clients = Client::factory()->count(8)->create([
            'coach_id' => $this->coach->id,
            'autoshare_workout' => 1,
        ]);

        foreach ($clients as $client) {
            WorkoutSession::factory()->create([
                'client_id' => $client->id,
                'completed' => true,
                'session_date' => Carbon::today(),
                'updated_at' => Carbon::now()->subMinutes(rand(5, 50)),
                'total_volume_kg' => 300,
            ]);
        }

        $events = $this->aggregator->buildFeed($this->coach->id, 'today', 'all');

        $aggregateEvent = collect($events)->firstWhere('type', 'aggregate');

        expect($aggregateEvent)->not()->toBeNull();
        expect($aggregateEvent['people_count'])->toBe(8);
        expect($aggregateEvent['headline'])->toContain('8 personas');
        expect($aggregateEvent['extra'])->toContain('kg');
    });

    it('does NOT aggregate when 5 or fewer workouts', function () {
        $clients = Client::factory()->count(3)->create([
            'coach_id' => $this->coach->id,
            'autoshare_workout' => 1,
        ]);

        foreach ($clients as $client) {
            WorkoutSession::factory()->create([
                'client_id' => $client->id,
                'completed' => true,
                'session_date' => Carbon::today(),
                'updated_at' => Carbon::now()->subMinutes(20),
            ]);
        }

        $events = $this->aggregator->buildFeed($this->coach->id, 'today', 'all');
        $aggregate = collect($events)->firstWhere('type', 'aggregate');

        expect($aggregate)->toBeNull();
    });
});
