<?php

declare(strict_types=1);

use App\Enums\ClientStatus;
use App\Enums\UserRole;
use App\Events\MembershipExtendedByCoach;
use App\Models\Admin;
use App\Models\AssignedPlan;
use App\Models\AuthToken;
use App\Models\Client;
use App\Models\PlanExtension;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;

uses(DatabaseTransactions::class);

if (! function_exists('wcExtendAuthHeader')) {
    function wcExtendAuthHeader(Admin $admin): array
    {
        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type' => 'admin',
            'user_id' => $admin->id,
            'token' => $token,
            'ip_address' => '127.0.0.1',
            'expires_at' => now()->addDay(),
            'created_at' => now(),
        ]);

        return ['Authorization' => "Bearer {$token}"];
    }
}

it('superadmin extiende plan vencido y status pasa a activo', function () {
    $superadmin = Admin::factory()->create(['role' => UserRole::Superadmin->value]);
    $client = Client::factory()->create([
        'plan' => 'esencial',
        'status' => ClientStatus::Inactivo->value,
    ]);

    AssignedPlan::create([
        'client_id' => $client->id,
        'plan_type' => 'entrenamiento',
        'content' => json_encode(['stub' => true]),
        'version' => 1,
        'active' => true,
        'assigned_by' => $superadmin->id,
        'valid_from' => now()->subDays(60)->toDateString(),
        'expires_at' => now()->subDays(30)->toDateString(),
    ]);

    $newDate = now()->addDays(30)->toDateString();

    $this->withHeaders(wcExtendAuthHeader($superadmin))
        ->postJson("/api/v/admin/clients/{$client->id}/extend-membership", [
            'new_expires_at' => $newDate,
            'notes' => 'Pago confirmado por transferencia',
        ])
        ->assertOk()
        ->assertJsonPath('extended', true)
        ->assertJsonPath('new_expires_at', $newDate)
        ->assertJsonPath('is_locked', false);

    $plan = AssignedPlan::where('client_id', $client->id)->first();
    expect($plan->expires_at->toDateString())->toBe($newDate);

    $client->refresh();
    expect($client->status)->toBe(ClientStatus::Activo);

    expect(PlanExtension::where('client_id', $client->id)->count())->toBe(1);
});

it('fecha custom NO preserva días pre-pagados (reemplaza directo)', function () {
    $superadmin = Admin::factory()->create(['role' => UserRole::Superadmin->value]);
    $client = Client::factory()->create([
        'plan' => 'metodo',
        'status' => ClientStatus::Activo->value,
    ]);

    // Plan que aún tiene 15 días pre-pagados
    AssignedPlan::create([
        'client_id' => $client->id,
        'plan_type' => 'entrenamiento',
        'content' => json_encode(['stub' => true]),
        'version' => 1,
        'active' => true,
        'assigned_by' => $superadmin->id,
        'valid_from' => now()->subDays(15)->toDateString(),
        'expires_at' => now()->addDays(15)->toDateString(),
    ]);

    $newDate = now()->addDays(10)->toDateString();

    $this->withHeaders(wcExtendAuthHeader($superadmin))
        ->postJson("/api/v/admin/clients/{$client->id}/extend-membership", [
            'new_expires_at' => $newDate,
        ])
        ->assertOk();

    $plan = AssignedPlan::where('client_id', $client->id)->first();
    expect($plan->expires_at->toDateString())->toBe($newDate);
});

it('status suspendido NO se toca al extender', function () {
    $superadmin = Admin::factory()->create(['role' => UserRole::Superadmin->value]);
    $client = Client::factory()->create([
        'plan' => 'elite',
        'status' => ClientStatus::Suspendido->value,
    ]);

    AssignedPlan::create([
        'client_id' => $client->id,
        'plan_type' => 'entrenamiento',
        'content' => json_encode(['stub' => true]),
        'version' => 1,
        'active' => true,
        'assigned_by' => $superadmin->id,
        'valid_from' => now()->subDays(60)->toDateString(),
        'expires_at' => now()->subDays(30)->toDateString(),
    ]);

    $this->withHeaders(wcExtendAuthHeader($superadmin))
        ->postJson("/api/v/admin/clients/{$client->id}/extend-membership", [
            'new_expires_at' => now()->addDays(30)->toDateString(),
        ])
        ->assertOk();

    $client->refresh();
    expect($client->status)->toBe(ClientStatus::Suspendido);
});

it('coach puede extender SOLO sus clientes (legacy coach_id)', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $client = Client::factory()->create([
        'plan' => 'esencial',
        'coach_id' => $coach->id,
    ]);

    AssignedPlan::create([
        'client_id' => $client->id,
        'plan_type' => 'entrenamiento',
        'content' => json_encode(['stub' => true]),
        'version' => 1,
        'active' => true,
        'assigned_by' => $coach->id,
        'valid_from' => now()->subDays(40)->toDateString(),
        'expires_at' => now()->subDays(10)->toDateString(),
    ]);

    $this->withHeaders(wcExtendAuthHeader($coach))
        ->postJson("/api/v/admin/clients/{$client->id}/extend-membership", [
            'new_expires_at' => now()->addDays(30)->toDateString(),
        ])
        ->assertOk();
});

it('coach NO puede extender cliente ajeno → 403', function () {
    $coachA = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $coachB = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $client = Client::factory()->create([
        'plan' => 'esencial',
        'coach_id' => $coachB->id,
    ]);

    AssignedPlan::create([
        'client_id' => $client->id,
        'plan_type' => 'entrenamiento',
        'content' => json_encode(['stub' => true]),
        'version' => 1,
        'active' => true,
        'assigned_by' => $coachB->id,
        'valid_from' => now()->subDays(40)->toDateString(),
        'expires_at' => now()->subDays(10)->toDateString(),
    ]);

    $this->withHeaders(wcExtendAuthHeader($coachA))
        ->postJson("/api/v/admin/clients/{$client->id}/extend-membership", [
            'new_expires_at' => now()->addDays(30)->toDateString(),
        ])
        ->assertForbidden();
});

it('fecha en el pasado → 422', function () {
    $superadmin = Admin::factory()->create(['role' => UserRole::Superadmin->value]);
    $client = Client::factory()->create(['plan' => 'esencial']);

    $this->withHeaders(wcExtendAuthHeader($superadmin))
        ->postJson("/api/v/admin/clients/{$client->id}/extend-membership", [
            'new_expires_at' => now()->subDay()->toDateString(),
        ])
        ->assertStatus(422);
});

it('fecha demasiado lejos en el futuro → 422', function () {
    $superadmin = Admin::factory()->create(['role' => UserRole::Superadmin->value]);
    $client = Client::factory()->create(['plan' => 'esencial']);

    $this->withHeaders(wcExtendAuthHeader($superadmin))
        ->postJson("/api/v/admin/clients/{$client->id}/extend-membership", [
            'new_expires_at' => now()->addYears(3)->toDateString(),
        ])
        ->assertStatus(422);
});

it('NO dispara event cuando el actor es superadmin', function () {
    Event::fake([MembershipExtendedByCoach::class]);

    $superadmin = Admin::factory()->create(['role' => UserRole::Superadmin->value]);
    $client = Client::factory()->create(['plan' => 'esencial']);
    AssignedPlan::create([
        'client_id' => $client->id,
        'plan_type' => 'entrenamiento',
        'content' => json_encode(['stub' => true]),
        'version' => 1,
        'active' => true,
        'assigned_by' => $superadmin->id,
        'valid_from' => now()->subDays(60)->toDateString(),
        'expires_at' => now()->subDays(10)->toDateString(),
    ]);

    $this->withHeaders(wcExtendAuthHeader($superadmin))
        ->postJson("/api/v/admin/clients/{$client->id}/extend-membership", [
            'new_expires_at' => now()->addDays(30)->toDateString(),
        ])
        ->assertOk();

    Event::assertNotDispatched(MembershipExtendedByCoach::class);
});

it('SÍ dispara event cuando el actor es coach', function () {
    Event::fake([MembershipExtendedByCoach::class]);

    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $client = Client::factory()->create([
        'plan' => 'esencial',
        'coach_id' => $coach->id,
    ]);
    AssignedPlan::create([
        'client_id' => $client->id,
        'plan_type' => 'entrenamiento',
        'content' => json_encode(['stub' => true]),
        'version' => 1,
        'active' => true,
        'assigned_by' => $coach->id,
        'valid_from' => now()->subDays(60)->toDateString(),
        'expires_at' => now()->subDays(10)->toDateString(),
    ]);

    $this->withHeaders(wcExtendAuthHeader($coach))
        ->postJson("/api/v/admin/clients/{$client->id}/extend-membership", [
            'new_expires_at' => now()->addDays(30)->toDateString(),
        ])
        ->assertOk();

    Event::assertDispatched(MembershipExtendedByCoach::class);
});
