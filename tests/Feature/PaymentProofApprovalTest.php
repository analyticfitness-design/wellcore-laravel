<?php

/**
 * POST /api/v/admin/payment-proofs/{id}/approve
 *
 * Guard: auth:wellcore (Bearer token) + role:admin,superadmin,jefe
 * Action: ApprovePaymentProofAction — DB transaction creates CoachInvitation,
 *         Payment, Client (or reuses existing), ClientCoach, updates proof status.
 */

use App\Enums\PaymentProofStatus;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\ClientCoach;
use App\Models\CoachInvitation;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\WellcoreNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function actingAsAdmin(Admin $admin): Tests\TestCase
{
    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type'  => 'admin',
        'user_id'    => $admin->id,
        'token'      => $token,
        'expires_at' => now()->addDay(),
        'created_at' => now(),
    ]);

    return test()->withHeaders(['Authorization' => "Bearer {$token}"]);
}

function makeAdminUser(UserRole $role = UserRole::Admin): Admin
{
    return Admin::factory()->create(['role' => $role->value]);
}

// ---------------------------------------------------------------------------
// T1 — Admin can approve a pending proof → 200
// ---------------------------------------------------------------------------

it('admin can approve a pending proof and receives 200', function () {
    Mail::fake();
    Storage::fake('payment_proofs');

    $admin = makeAdminUser();
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    $response = actingAsAdmin($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/approve");

    $response->assertStatus(200)
             ->assertJsonPath('message', 'Comprobante aprobado.');

    $proof->refresh();
    expect($proof->status)->toBe(PaymentProofStatus::Aprobado)
        ->and($proof->reviewed_by)->toBe($admin->id)
        ->and($proof->reviewed_at)->not->toBeNull();
});

// ---------------------------------------------------------------------------
// T2 — Approving creates CoachInvitation + Payment + ClientCoach with correct source
// ---------------------------------------------------------------------------

it('approval creates CoachInvitation, Payment, and ClientCoach with source payment_proof', function () {
    Mail::fake();
    Storage::fake('payment_proofs');

    $admin = makeAdminUser();
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create([
        'client_email' => 'newclient-' . uniqid() . '@wellcore.test',
        'client_name'  => 'Juan Prueba',
    ]);

    actingAsAdmin($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/approve")
        ->assertStatus(200);

    // CoachInvitation created
    $this->assertDatabaseHas('coach_invitations', [
        'coach_id' => $coach->id,
        'email'    => $proof->client_email,
        'status'   => 'paid',
    ]);

    // Payment created with correct email
    $this->assertDatabaseHas('payments', [
        'email'  => $proof->client_email,
        'status' => 'APPROVED',
    ]);

    // Client exists
    $this->assertDatabaseHas('clients', [
        'email' => $proof->client_email,
    ]);

    // ClientCoach record with source=payment_proof and active=true
    $invitation = CoachInvitation::where('email', $proof->client_email)
        ->where('coach_id', $coach->id)
        ->firstOrFail();

    $this->assertDatabaseHas('client_coach', [
        'admin_id'           => $coach->id,
        'source'             => 'payment_proof',
        'coach_invitation_id' => $invitation->id,
        'active'             => 1,
    ]);
});

// ---------------------------------------------------------------------------
// T3 — Coach receives in-app notification after approval
// ---------------------------------------------------------------------------

it('coach receives a WellcoreNotification of type payment_proof_approved after approval', function () {
    Mail::fake();
    Storage::fake('payment_proofs');

    $admin = makeAdminUser();
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    actingAsAdmin($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/approve")
        ->assertStatus(200);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $coach->id,
        'type'    => 'payment_proof_approved',
    ]);
});

// ---------------------------------------------------------------------------
// T4 — Already-approved proof is not found (404) because query filters status=pendiente
// ---------------------------------------------------------------------------

it('returns 404 when trying to approve an already-approved proof', function () {
    $admin = makeAdminUser();
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->aprobado()->forCoach($coach)->create();

    $response = actingAsAdmin($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/approve");

    $response->assertStatus(404);
});

// ---------------------------------------------------------------------------
// T5 — Rejected proof returns 404 (query filters status=pendiente)
// ---------------------------------------------------------------------------

it('returns 404 when trying to approve a rejected proof', function () {
    $admin = makeAdminUser();
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->rechazado()->forCoach($coach)->create();

    $response = actingAsAdmin($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/approve");

    $response->assertStatus(404);
});

// ---------------------------------------------------------------------------
// T6 — Coach role cannot call approve endpoint (403)
// ---------------------------------------------------------------------------

it('coach cannot approve a payment proof (403)', function () {
    $coach       = Admin::factory()->coach()->create();
    $otherCoach  = Admin::factory()->coach()->create();
    $proof       = PaymentProof::factory()->pendiente()->forCoach($otherCoach)->create();

    // actingAsAdmin helper works for any Admin — role is enforced by middleware
    $response = actingAsAdmin($coach)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/approve");

    // The v/admin route middleware requires role:admin,superadmin,jefe — coach is blocked
    $response->assertStatus(403);
});

// ---------------------------------------------------------------------------
// T7 — Existing client is reused (not duplicated) when approving
// ---------------------------------------------------------------------------

it('approval reuses existing client when email already exists', function () {
    Mail::fake();
    Storage::fake('payment_proofs');

    $admin          = makeAdminUser();
    $coach          = Admin::factory()->coach()->create();
    $existingEmail  = 'existing-' . uniqid() . '@wellcore.test';

    // Pre-existing client with same email
    \App\Models\Client::factory()->create(['email' => $existingEmail]);

    $clientCountBefore = \App\Models\Client::where('email', $existingEmail)->count();

    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create([
        'client_email' => $existingEmail,
    ]);

    actingAsAdmin($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/approve")
        ->assertStatus(200);

    // No duplicate client created
    $clientCountAfter = \App\Models\Client::where('email', $existingEmail)->count();
    expect($clientCountAfter)->toBe($clientCountBefore);
});

// ---------------------------------------------------------------------------
// T8 — Previous ClientCoach records are deactivated on approval
// ---------------------------------------------------------------------------

it('approval deactivates previous client_coach records before creating new one', function () {
    Mail::fake();
    Storage::fake('payment_proofs');

    $admin       = makeAdminUser();
    $coach       = Admin::factory()->coach()->create();
    $oldCoach    = Admin::factory()->coach()->create();
    $clientEmail = 'switch-coach-' . uniqid() . '@wellcore.test';

    $client = \App\Models\Client::factory()->create(['email' => $clientEmail]);

    // Client was previously assigned to a different coach (active)
    ClientCoach::create([
        'client_id'   => $client->id,
        'admin_id'    => $oldCoach->id,
        'source'      => 'invitation',
        'assigned_at' => now()->subMonth(),
        'active'      => true,
    ]);

    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create([
        'client_email' => $clientEmail,
    ]);

    actingAsAdmin($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/approve")
        ->assertStatus(200);

    // Old assignment deactivated
    $this->assertDatabaseHas('client_coach', [
        'client_id' => $client->id,
        'admin_id'  => $oldCoach->id,
        'active'    => 0,
    ]);

    // New assignment active with correct source
    $this->assertDatabaseHas('client_coach', [
        'client_id' => $client->id,
        'admin_id'  => $coach->id,
        'source'    => 'payment_proof',
        'active'    => 1,
    ]);
});
