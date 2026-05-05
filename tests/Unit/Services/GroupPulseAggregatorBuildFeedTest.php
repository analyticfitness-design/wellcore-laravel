<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\PersonalRecord;
use App\Services\GroupPulseAggregator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

describe('GroupPulseAggregator::buildFeed', function () {
    beforeEach(function () {
        $this->coach = Admin::factory()->create();
        $this->aggregator = new GroupPulseAggregator;
    });

    it('returns PR events as individual entries', function () {
        $client = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'name' => 'Carlos Rojas',
            'autoshare_pr' => 1,
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $client->id,
            'exercise' => 'Sentadilla',
            'weight' => 120,
            'reps' => 5,
            'is_current' => 1,
            'created_at' => Carbon::now()->subMinutes(8),
        ]);

        $events = $this->aggregator->buildFeed($this->coach->id, 'today', 'all');

        expect($events)->toHaveCount(1);
        expect($events[0])->toMatchArray([
            'type' => 'pr',
            'client_name' => 'Carlos R.',
            'client_initials' => 'CR',
        ]);
        expect($events[0]['headline'])->toContain('Sentadilla');
        expect($events[0]['headline'])->toContain('120');
    });

    it('filters by time window today', function () {
        $client = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'autoshare_pr' => 1,
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $client->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $client->id,
            'is_current' => 1,
            'created_at' => Carbon::today()->subDays(3),
        ]);

        $todayEvents = $this->aggregator->buildFeed($this->coach->id, 'today', 'all');
        $weekEvents = $this->aggregator->buildFeed($this->coach->id, 'week', 'all');

        expect($todayEvents)->toHaveCount(1);
        expect($weekEvents)->toHaveCount(2);
    });

    it('returns empty array for coach without clients', function () {
        $events = $this->aggregator->buildFeed($this->coach->id, 'today', 'all');
        expect($events)->toBeArray()->toBeEmpty();
    });
});
