<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\CoachContentDrop;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

uses(DatabaseTransactions::class);

function actingAsAdminQueue(Admin $admin): TestCase
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

it('admin can list the drops queue with meta', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    CoachContentDrop::factory()->inReview()->create(['coach_id' => $coach->id]);

    actingAsAdminQueue($admin)
        ->getJson('/api/v/admin/marketing/drops')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'total', 'pending_review_count', 'coaches_without_drop_this_week'],
        ]);
});

it('coach role cannot access queue (forbidden)', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);

    actingAsAdminQueue($coach)
        ->getJson('/api/v/admin/marketing/drops')
        ->assertForbidden();
});

it('queue filters by status', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $coach1 = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $coach2 = Admin::factory()->create(['role' => UserRole::Coach->value]);
    CoachContentDrop::factory()->inReview()->create(['coach_id' => $coach1->id]);
    CoachContentDrop::factory()->ready()->create(['coach_id' => $coach2->id]);

    $resp = actingAsAdminQueue($admin)
        ->getJson('/api/v/admin/marketing/drops?status=in_review')
        ->assertOk();

    expect(collect($resp->json('data'))->every(fn ($r) => $r['status'] === 'in_review'))->toBeTrue();
});
