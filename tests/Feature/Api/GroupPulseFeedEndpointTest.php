<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\PersonalRecord;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

describe('GET /api/v/client/group-pulse?scope=feed', function () {
    beforeEach(function () {
        $this->coach = Admin::factory()->create();
        $this->client = Client::factory()->create(['coach_id' => $this->coach->id]);
    });

    it('paginates events correctly', function () {
        $other = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'autoshare_pr' => 1,
        ]);
        PersonalRecord::factory()->count(15)->create([
            'client_id' => $other->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);

        actingAsClient($this->client);

        $response = $this->getJson('/api/v/client/group-pulse?scope=feed&page=1&per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure(['events', 'pagination' => ['current_page', 'last_page', 'total']])
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.total', 15);

        expect($response->json('events'))->toHaveCount(10);
    });

    it('respects time filter', function () {
        $other = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'autoshare_pr' => 1,
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $other->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $other->id,
            'is_current' => 1,
            'created_at' => Carbon::today()->subDays(3),
        ]);

        actingAsClient($this->client);

        $today = $this->getJson('/api/v/client/group-pulse?scope=feed&time=today');
        $week = $this->getJson('/api/v/client/group-pulse?scope=feed&time=week');

        expect($today->json('pagination.total'))->toBe(1);
        expect($week->json('pagination.total'))->toBe(2);
    });
});
