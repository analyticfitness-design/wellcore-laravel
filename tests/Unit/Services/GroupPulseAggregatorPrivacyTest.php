<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\PersonalRecord;
use App\Services\GroupPulseAggregator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

describe('GroupPulseAggregator privacy flags', function () {
    beforeEach(function () {
        $this->coach = Admin::factory()->create();
        $this->aggregator = new GroupPulseAggregator;
    });

    it('hides PRs when client has autoshare_pr=0', function () {
        $hidden = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'autoshare_pr' => 0,
        ]);
        $visible = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'autoshare_pr' => 1,
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $hidden->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $visible->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);

        $events = $this->aggregator->buildFeed($this->coach->id, 'today', 'all');
        $prEvents = collect($events)->where('type', 'pr');

        expect($prEvents)->toHaveCount(1);
    });
});
