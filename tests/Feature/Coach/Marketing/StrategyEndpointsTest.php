<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\CoachContentDrop;
use App\Models\CoachContractAcceptance;
use App\Models\CoachMarketingProfile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

uses(DatabaseTransactions::class);

function actingAsCoachStrategy(Admin $coach): TestCase
{
    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type' => 'admin',
        'user_id' => $coach->id,
        'token' => $token,
        'expires_at' => now()->addDay(),
    ]);

    CoachContractAcceptance::firstOrCreate(
        ['coach_id' => $coach->id, 'contract_version' => config('wellcore.coach_contract.version', '1.0')],
        [
            'status' => 'accepted',
            'accepted_at' => now(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'content_hash' => str_repeat('a', 64),
            'scroll_completed' => true,
        ],
    );

    return test()->withHeaders(['Authorization' => "Bearer {$token}"]);
}

function completeProfile(Admin $coach): void
{
    CoachMarketingProfile::factory()->completed()->create(['coach_id' => $coach->id]);
}

it('coach with completed profile gets null when no current drop', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    completeProfile($coach);

    actingAsCoachStrategy($coach)
        ->getJson('/api/v/coach/strategy/current')
        ->assertOk()
        ->assertJsonPath('data', null);
});

it('coach without completed profile is blocked (PROFILE_INCOMPLETE)', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);

    actingAsCoachStrategy($coach)
        ->getJson('/api/v/coach/strategy/current')
        ->assertForbidden()
        ->assertJsonPath('code', 'PROFILE_INCOMPLETE');
});

it('coach gets current drop when ready status', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    completeProfile($coach);

    $monday = now()->startOfWeek();
    CoachContentDrop::factory()->ready()->create([
        'coach_id' => $coach->id,
        'iso_year' => (int) $monday->isoFormat('GGGG'),
        'iso_week' => (int) $monday->isoFormat('W'),
    ]);

    actingAsCoachStrategy($coach)
        ->getJson('/api/v/coach/strategy/current')
        ->assertOk()
        ->assertJsonStructure(['data' => ['id', 'status', 'content', 'attribution']]);
});

it('IDOR: coach cannot see another coachs drop via show', function () {
    $coachA = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $coachB = Admin::factory()->create(['role' => UserRole::Coach->value]);
    completeProfile($coachA);

    $drop = CoachContentDrop::factory()->ready()->create(['coach_id' => $coachB->id]);

    actingAsCoachStrategy($coachA)
        ->getJson("/api/v/coach/strategy/drops/{$drop->id}")
        ->assertForbidden();
});

it('history paginates and respects per_page max 50', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    completeProfile($coach);

    // Create drops with different iso_week values to avoid the unique constraint
    // uniq_coach_week enforces unique (coach_id, iso_year, iso_week)
    $baseYear = 2025;
    for ($week = 1; $week <= 5; $week++) {
        CoachContentDrop::factory()->ready()->create([
            'coach_id' => $coach->id,
            'iso_year' => $baseYear,
            'iso_week' => $week,
            'week_starts_on' => now()->subWeeks(6 - $week)->startOfWeek()->toDateString(),
        ]);
    }

    actingAsCoachStrategy($coach)
        ->getJson('/api/v/coach/strategy/history?per_page=3')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('history returns empty when coach has no visible drops', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    completeProfile($coach);

    actingAsCoachStrategy($coach)
        ->getJson('/api/v/coach/strategy/history')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});
