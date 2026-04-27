<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\CoachContentDrop;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

uses(DatabaseTransactions::class);

function actingAsAdminReview(Admin $admin): TestCase
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

it('admin can view a drop with sensitive fields', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $drop = CoachContentDrop::factory()->inReview()->create();

    actingAsAdminReview($admin)
        ->getJson("/api/v/admin/marketing/drops/{$drop->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => ['id', 'status', 'content', 'intake_snapshot', 'original_content']]);
});

it('coach cannot view drop via admin endpoint', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $drop = CoachContentDrop::factory()->inReview()->create();

    actingAsAdminReview($coach)
        ->getJson("/api/v/admin/marketing/drops/{$drop->id}")
        ->assertForbidden();
});

it('approve transitions drop to ready status', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $drop = CoachContentDrop::factory()->inReview()->create();

    actingAsAdminReview($admin)
        ->postJson("/api/v/admin/marketing/drops/{$drop->id}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', 'ready');
});

it('requestRegenerate sends drop back to pending', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $drop = CoachContentDrop::factory()->inReview()->create();

    actingAsAdminReview($admin)
        ->postJson("/api/v/admin/marketing/drops/{$drop->id}/request-regenerate")
        ->assertOk()
        ->assertJsonPath('data.status', 'pending');
});

it('updateContent rejects invalid JSON payload (422)', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $drop = CoachContentDrop::factory()->inReview()->create();

    actingAsAdminReview($admin)
        ->putJson("/api/v/admin/marketing/drops/{$drop->id}/content", [
            'content' => ['schema_version' => 'invalid_version_xyz'],
        ])
        ->assertUnprocessable();
});
