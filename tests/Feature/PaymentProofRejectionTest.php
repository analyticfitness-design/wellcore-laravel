<?php

/**
 * POST /api/v/admin/payment-proofs/{id}/reject
 *
 * Guard: auth:wellcore (Bearer token) + role:admin,superadmin,jefe
 * Action: RejectPaymentProofAction — sets status=rechazado, sends mail to coach,
 *         creates in-app notification, writes audit log.
 */

use App\Enums\PaymentProofStatus;
use App\Enums\UserRole;
use App\Mail\PaymentProofRejected;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\PaymentProof;
use Illuminate\Support\Facades\Mail;

// ---------------------------------------------------------------------------
// Helpers (scoped to this file — no conflict with ApprovalTest)
// ---------------------------------------------------------------------------

function actingAsAdminForRejection(Admin $admin): Tests\TestCase
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

function makeAdminUserForRejection(UserRole $role = UserRole::Admin): Admin
{
    return Admin::factory()->create(['role' => $role->value]);
}

// ---------------------------------------------------------------------------
// T1 — Admin can reject a pending proof with a reason → 200
// ---------------------------------------------------------------------------

it('admin can reject a pending proof with a review note and receives 200', function () {
    Mail::fake();

    $admin = makeAdminUserForRejection();
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    $response = actingAsAdminForRejection($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/reject", [
            'review_note' => 'El monto no coincide con el plan.',
        ]);

    $response->assertStatus(200)
             ->assertJsonPath('message', 'Comprobante rechazado.');

    $proof->refresh();
    expect($proof->status)->toBe(PaymentProofStatus::Rechazado)
        ->and($proof->review_note)->toBe('El monto no coincide con el plan.')
        ->and($proof->reviewed_by)->toBe($admin->id)
        ->and($proof->reviewed_at)->not->toBeNull();
});

// ---------------------------------------------------------------------------
// T2 — Rejecting without review_note returns 422
// ---------------------------------------------------------------------------

it('returns 422 when review_note is missing on reject', function () {
    $admin = makeAdminUserForRejection();
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    $response = actingAsAdminForRejection($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/reject", []);

    $response->assertStatus(422)
             ->assertJsonValidationErrorFor('review_note');
});

// ---------------------------------------------------------------------------
// T3 — Coach receives in-app notification after rejection
// ---------------------------------------------------------------------------

it('coach receives a WellcoreNotification of type payment_proof_rejected after rejection', function () {
    Mail::fake();

    $admin = makeAdminUserForRejection();
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    actingAsAdminForRejection($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/reject", [
            'review_note' => 'Imagen ilegible.',
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $coach->id,
        'type'    => 'payment_proof_rejected',
    ]);
});

// ---------------------------------------------------------------------------
// T4 — Rejection mail is queued to the coach's email
// ---------------------------------------------------------------------------

it('queues a PaymentProofRejected mail to the coach after rejection', function () {
    Mail::fake();

    $admin = makeAdminUserForRejection();
    $coach = Admin::factory()->coach()->create(['email' => 'coach-' . uniqid() . '@wellcore.test']);
    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    actingAsAdminForRejection($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/reject", [
            'review_note' => 'Comprobante ilegible.',
        ])
        ->assertStatus(200);

    Mail::assertQueued(PaymentProofRejected::class, function ($mail) use ($coach) {
        return $mail->hasTo($coach->email);
    });
});

// ---------------------------------------------------------------------------
// T5 — Status changes to rechazado in the database
// ---------------------------------------------------------------------------

it('proof status is persisted as rechazado in the database after rejection', function () {
    Mail::fake();

    $admin = makeAdminUserForRejection();
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    actingAsAdminForRejection($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/reject", [
            'review_note' => 'Foto borrosa.',
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('payment_proofs', [
        'id'          => $proof->id,
        'status'      => PaymentProofStatus::Rechazado->value,
        'review_note' => 'Foto borrosa.',
    ]);
});

// ---------------------------------------------------------------------------
// T6 — Cannot reject an already-rejected proof (404 — query filters pendiente only)
// ---------------------------------------------------------------------------

it('returns 404 when trying to reject an already-rejected proof', function () {
    $admin = makeAdminUserForRejection();
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->rechazado()->forCoach($coach)->create();

    $response = actingAsAdminForRejection($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/reject", [
            'review_note' => 'Segunda razón.',
        ]);

    $response->assertStatus(404);
});

// ---------------------------------------------------------------------------
// T7 — Cannot reject an already-approved proof (404)
// ---------------------------------------------------------------------------

it('returns 404 when trying to reject an approved proof', function () {
    $admin = makeAdminUserForRejection();
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->aprobado()->forCoach($coach)->create();

    $response = actingAsAdminForRejection($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/reject", [
            'review_note' => 'Razón tardía.',
        ]);

    $response->assertStatus(404);
});

// ---------------------------------------------------------------------------
// T8 — Coach role cannot call reject endpoint (403 — not in role:admin,superadmin,jefe)
// ---------------------------------------------------------------------------

it('coach cannot reject a payment proof (403)', function () {
    $coach      = Admin::factory()->coach()->create();
    $otherCoach = Admin::factory()->coach()->create();
    $proof      = PaymentProof::factory()->pendiente()->forCoach($otherCoach)->create();

    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type'  => 'admin',
        'user_id'    => $coach->id,
        'token'      => $token,
        'expires_at' => now()->addDay(),
        'created_at' => now(),
    ]);

    $response = test()
        ->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/reject", [
            'review_note' => 'Intento no autorizado.',
        ]);

    $response->assertStatus(403);
});

// ---------------------------------------------------------------------------
// T9 — review_note longer than 500 chars returns 422
// ---------------------------------------------------------------------------

it('returns 422 when review_note exceeds 500 characters', function () {
    $admin = makeAdminUserForRejection();
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    $response = actingAsAdminForRejection($admin)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/reject", [
            'review_note' => str_repeat('A', 501),
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrorFor('review_note');
});
