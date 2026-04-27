<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

uses(DatabaseTransactions::class);

function actingAsAdminProfile(Admin $admin): TestCase
{
    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type' => 'admin',
        'user_id' => $admin->id,
        'token' => $token,
        'expires_at' => now()->addDay(),
    ]);

    return test()->withHeaders(['Authorization' => "Bearer {$token}"]);
}

it('admin can view coach marketing profile', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);

    actingAsAdminProfile($admin)
        ->getJson("/api/v/admin/coaches/{$coach->id}/marketing-profile")
        ->assertOk()
        ->assertJsonPath('data', null);
});

it('admin can update coach marketing profile with audit fields', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);

    $payload = [
        'brand_name' => 'Coach Fitness Pro',
        'city' => 'Medellin',
        'country_code' => 'CO',
        'specialty_primary' => 'hipertrofia',
        'differentiator' => 'Especialista en ganancia muscular para hombres adultos mayores',
        'audience_age_range' => '35-45',
        'audience_gender' => 'hombres',
        'audience_pain_main' => 'Falta de masa muscular y energia',
        'audience_offer_main' => 'elite',
        'preferred_methodologies' => ['sobrecarga_progresiva'],
        'content_topics' => ['transformaciones'],
        'voice_adjectives' => ['serio', 'tecnico', 'motivador'],
        'active_offers' => [['name' => 'Elite', 'price' => 150, 'currency' => 'USD', 'promo' => null]],
    ];

    actingAsAdminProfile($admin)
        ->putJson("/api/v/admin/coaches/{$coach->id}/marketing-profile", $payload)
        ->assertOk()
        ->assertJsonPath('data.brand_name', 'Coach Fitness Pro')
        ->assertJsonPath('data.is_complete', true);
});

it('coach role cannot access admin coach profile endpoint', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $targetCoach = Admin::factory()->create(['role' => UserRole::Coach->value]);

    actingAsAdminProfile($coach)
        ->getJson("/api/v/admin/coaches/{$targetCoach->id}/marketing-profile")
        ->assertForbidden();
});

it('returns 404 when trying to access non-coach admin via coach profile endpoint', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $anotherAdmin = Admin::factory()->create(['role' => UserRole::Admin->value]);

    actingAsAdminProfile($admin)
        ->getJson("/api/v/admin/coaches/{$anotherAdmin->id}/marketing-profile")
        ->assertNotFound();
});
