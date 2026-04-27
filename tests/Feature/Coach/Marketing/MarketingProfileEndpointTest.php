<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\CoachContractAcceptance;
use App\Models\CoachMarketingProfile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

uses(DatabaseTransactions::class);

function actingAsCoachMarketing(Admin $coach): TestCase
{
    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type' => 'admin',
        'user_id' => $coach->id,
        'token' => $token,
        'expires_at' => now()->addDay(),
    ]);

    // Satisfy the contract gate so the coach can access protected routes.
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

it('returns null when coach has no profile', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);

    actingAsCoachMarketing($coach)
        ->getJson('/api/v/coach/marketing-profile')
        ->assertOk()
        ->assertJsonPath('data', null);
});

it('coach can submit complete profile and gets is_complete=true', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);

    $payload = [
        'brand_name' => 'Coach FIT',
        'city' => 'Bogotá',
        'country_code' => 'CO',
        'specialty_primary' => 'fuerza',
        'differentiator' => 'Especializado en fuerza para mujeres mayores de 30',
        'audience_age_range' => '25-35',
        'audience_gender' => 'mujeres',
        'audience_pain_main' => 'No saben cómo progresar',
        'audience_offer_main' => 'metodo',
        'preferred_methodologies' => ['sobrecarga_progresiva'],
        'content_topics' => ['mitos_fitness'],
        'voice_adjectives' => ['directo', 'tecnico', 'cercano'],
        'active_offers' => [['name' => 'Método', 'price' => 120, 'currency' => 'USD', 'promo' => null]],
    ];

    actingAsCoachMarketing($coach)
        ->putJson('/api/v/coach/marketing-profile', $payload)
        ->assertOk()
        ->assertJsonPath('data.is_complete', true)
        ->assertJsonPath('data.brand_name', 'Coach FIT');
});

it('coach can save draft without completing profile', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);

    actingAsCoachMarketing($coach)
        ->patchJson('/api/v/coach/marketing-profile/draft', ['brand_name' => 'Draft Name'])
        ->assertOk()
        ->assertJsonPath('data.is_complete', false);
});

it('coach can retrieve their existing profile', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    CoachMarketingProfile::factory()->completed()->create(['coach_id' => $coach->id]);

    actingAsCoachMarketing($coach)
        ->getJson('/api/v/coach/marketing-profile')
        ->assertOk()
        ->assertJsonPath('data.is_complete', true);
});
