<?php

declare(strict_types=1);

use App\Enums\Marketing\DropStatus;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\CoachContentDrop;
use App\Models\CoachContractAcceptance;
use App\Models\CoachMarketingProfile;
use App\Services\Marketing\DropSchemaValidator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(DatabaseTransactions::class);

beforeEach(fn () => Cache::flush());

function e2eActAs(Admin $user): TestCase
{
    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type' => 'admin',
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => now()->addDay(),
    ]);

    if ($user->role === UserRole::Coach) {
        CoachContractAcceptance::firstOrCreate(
            ['coach_id' => $user->id, 'contract_version' => config('wellcore.coach_contract.version', '1.0')],
            [
                'status' => 'accepted',
                'accepted_at' => now(),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'content_hash' => str_repeat('a', 64),
                'scroll_completed' => true,
            ],
        );
    }

    // Reset session and forget guards so a previous actor's authenticated user
    // does not leak across requests (WellCoreGuard caches $this->user per instance,
    // and the AuthManager keeps the guard as a singleton across HTTP test requests).
    test()->flushSession();
    Auth::forgetGuards();

    return test()
        ->withoutHeader('Authorization')
        ->withHeader('Authorization', "Bearer {$token}");
}

it('end-to-end coach strategy flow: onboarding -> insert -> approve -> coach sees -> publish piece -> archive', function () {
    // ===== 1. Coach completa onboarding =====
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $payload = json_decode(
        file_get_contents(base_path('tests/fixtures/intake_complete.json')),
        true
    );

    e2eActAs($coach)
        ->putJson('/api/v/coach/marketing-profile', $payload)
        ->assertOk()
        ->assertJsonPath('data.is_complete', true);

    $profile = CoachMarketingProfile::where('coach_id', $coach->id)->firstOrFail();
    expect($profile->isComplete())->toBeTrue();

    // ===== 2. Sistema (admin via tinker) inserta drop in_review =====
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $monday = now()->startOfWeek();
    $content = json_decode(
        file_get_contents(base_path('tests/fixtures/coach_drop_v1_valid.json')),
        true
    );

    (new DropSchemaValidator)->validate($content);

    $drop = CoachContentDrop::create([
        'coach_id' => $coach->id,
        'iso_year' => (int) $monday->isoFormat('GGGG'),
        'iso_week' => (int) $monday->isoFormat('W'),
        'week_starts_on' => $monday->toDateString(),
        'status' => DropStatus::InReview->value,
        'content' => $content,
        'original_content' => $content,
        'intake_snapshot' => $profile->only(['brand_name', 'specialty_primary', 'audience_age_range']),
        'schema_version' => 'coach_drop_v1',
        'generated_at' => now(),
    ]);

    expect($drop->status)->toBe(DropStatus::InReview);

    // ===== 3. Admin aprueba el drop =====
    e2eActAs($admin)
        ->postJson("/api/v/admin/marketing/drops/{$drop->id}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', 'ready');

    expect($drop->fresh()->status)->toBe(DropStatus::Ready);

    // ===== 4. Coach ve el drop como current =====
    $resp = e2eActAs($coach)
        ->getJson('/api/v/coach/strategy/current')
        ->assertOk();

    expect($resp->json('data.id'))->toBe($drop->id)
        ->and($resp->json('data.attribution'))->toBe(config('marketing.attribution.line'));

    // ===== 5. Coach marca un reel como publicado =====
    e2eActAs($coach)
        ->postJson("/api/v/coach/strategy/drops/{$drop->id}/pieces/reel_1/publish", [
            'url' => 'https://instagram.com/p/abc123',
        ])
        ->assertOk()
        ->assertJsonPath('data.state', 'published');

    // ===== 6. Drop pasa a completed manualmente y archive job lo archiva =====
    $drop->update(['status' => DropStatus::Completed->value, 'completed_at' => now()->subDays(31)]);

    $this->artisan('wellcore:archive-old-drops')->assertSuccessful();

    expect($drop->fresh()->status)->toBe(DropStatus::Archived);
});
