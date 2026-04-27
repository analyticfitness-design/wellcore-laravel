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

function actingAsCoachPiece(Admin $coach): TestCase
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

it('coach can publish a reel piece', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    CoachMarketingProfile::factory()->completed()->create(['coach_id' => $coach->id]);
    $drop = CoachContentDrop::factory()->ready()->create(['coach_id' => $coach->id]);

    actingAsCoachPiece($coach)
        ->postJson("/api/v/coach/strategy/drops/{$drop->id}/pieces/reel_1/publish", [
            'url' => 'https://instagram.com/p/abc123',
        ])
        ->assertOk()
        ->assertJsonPath('data.state', 'published')
        ->assertJsonPath('data.piece_key', 'reel_1');
});

it('IDOR: coach cannot publish piece on another coachs drop', function () {
    $coachA = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $coachB = Admin::factory()->create(['role' => UserRole::Coach->value]);
    CoachMarketingProfile::factory()->completed()->create(['coach_id' => $coachA->id]);
    $drop = CoachContentDrop::factory()->ready()->create(['coach_id' => $coachB->id]);

    actingAsCoachPiece($coachA)
        ->postJson("/api/v/coach/strategy/drops/{$drop->id}/pieces/reel_1/publish", [
            'url' => 'https://instagram.com/p/abc',
        ])
        ->assertForbidden();
});

it('coach can skip a story piece', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    CoachMarketingProfile::factory()->completed()->create(['coach_id' => $coach->id]);
    $drop = CoachContentDrop::factory()->ready()->create(['coach_id' => $coach->id]);

    actingAsCoachPiece($coach)
        ->postJson("/api/v/coach/strategy/drops/{$drop->id}/pieces/story_LUN/skip")
        ->assertOk()
        ->assertJsonPath('data.state', 'skipped');
});

it('coach can mark a reel as in-progress', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    CoachMarketingProfile::factory()->completed()->create(['coach_id' => $coach->id]);
    $drop = CoachContentDrop::factory()->ready()->create(['coach_id' => $coach->id]);

    actingAsCoachPiece($coach)
        ->postJson("/api/v/coach/strategy/drops/{$drop->id}/pieces/reel_2/in-progress")
        ->assertOk()
        ->assertJsonPath('data.state', 'in_progress');
});
