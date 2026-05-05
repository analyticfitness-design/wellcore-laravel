<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\PersonalRecord;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

describe('GET /api/v/client/group-pulse?scope=summary', function () {
    beforeEach(function () {
        $this->coach = Admin::factory()->create();
        $this->client = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'status' => 'activo',
        ]);
    });

    it('returns summary shape with stats and top_events', function () {
        PersonalRecord::factory()->create([
            'client_id' => $this->client->id,
            'is_current' => 1,
            'created_at' => Carbon::now()->subMinutes(5),
        ]);

        actingAsClient($this->client);

        $response = $this->getJson('/api/v/client/group-pulse?scope=summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'active_now',
                'bpm',
                'stats' => ['workouts_today', 'prs_week', 'achievements_today', 'checkins_week'],
                'top_events',
                'user_vs_group',
            ]);
    });

    it('returns 204 when client has no coach', function () {
        $orphan = Client::factory()->create([
            'coach_id' => null,
            'status' => 'activo',
        ]);

        actingAsClient($orphan);

        $response = $this->getJson('/api/v/client/group-pulse?scope=summary');

        $response->assertStatus(204);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/v/client/group-pulse?scope=summary');
        $response->assertStatus(401);
    });
});
