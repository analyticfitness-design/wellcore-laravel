<?php

/**
 * Unit tests for ApprovePaymentProofAction.
 *
 * Uses DatabaseTransactions so no data leaks between tests.
 * All mail is faked — no real queuing.
 */

use App\Actions\ApprovePaymentProofAction;
use App\Enums\PaymentProofStatus;
use App\Enums\UserRole;
use App\Mail\PaymentProofApproved;
use App\Models\Admin;
use App\Models\CoachInvitation;
use App\Models\Payment;
use App\Models\PaymentProof;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;

uses(DatabaseTransactions::class);

// ---------------------------------------------------------------------------
// T1 — Throws BadMethodCallException when proof status != pendiente
// ---------------------------------------------------------------------------

it('throws BadMethodCallException when proof is already aprobado', function () {
    Mail::fake();

    $coach    = Admin::factory()->coach()->create();
    $reviewer = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $proof    = PaymentProof::factory()->aprobado()->forCoach($coach)->create();

    expect(fn () => (new ApprovePaymentProofAction)->handle($proof, $reviewer))
        ->toThrow(\BadMethodCallException::class);
});

it('throws BadMethodCallException when proof is already rechazado', function () {
    Mail::fake();

    $coach    = Admin::factory()->coach()->create();
    $reviewer = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $proof    = PaymentProof::factory()->rechazado()->forCoach($coach)->create();

    expect(fn () => (new ApprovePaymentProofAction)->handle($proof, $reviewer))
        ->toThrow(\BadMethodCallException::class);
});

it('throws BadMethodCallException when proof is expirado', function () {
    Mail::fake();

    $coach    = Admin::factory()->coach()->create();
    $reviewer = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $proof    = PaymentProof::factory()->expirado()->forCoach($coach)->create();

    expect(fn () => (new ApprovePaymentProofAction)->handle($proof, $reviewer))
        ->toThrow(\BadMethodCallException::class);
});

// ---------------------------------------------------------------------------
// T2 — Proof status is updated to aprobado after handle()
// ---------------------------------------------------------------------------

it('updates proof status to aprobado', function () {
    Mail::fake();

    $coach    = Admin::factory()->coach()->create();
    $reviewer = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $proof    = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    (new ApprovePaymentProofAction)->handle($proof, $reviewer);

    $proof->refresh();
    expect($proof->status)->toBe(PaymentProofStatus::Aprobado)
        ->and($proof->reviewed_by)->toBe($reviewer->id)
        ->and($proof->reviewed_at)->not->toBeNull();
});

// ---------------------------------------------------------------------------
// T3 — CoachInvitation is created with correct data
// ---------------------------------------------------------------------------

it('creates a CoachInvitation linked to the proof coach and client email', function () {
    Mail::fake();

    $coach    = Admin::factory()->coach()->create();
    $reviewer = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $email    = 'unit-test-' . uniqid() . '@wellcore.test';

    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create([
        'client_email' => $email,
    ]);

    (new ApprovePaymentProofAction)->handle($proof, $reviewer);

    $this->assertDatabaseHas('coach_invitations', [
        'coach_id' => $coach->id,
        'email'    => $email,
        'status'   => 'paid',
    ]);

    $invitation = CoachInvitation::where('email', $email)->where('coach_id', $coach->id)->first();
    // CoachInvitation casts plan to PlanType enum; compare value strings
    $invPlan = $invitation->plan instanceof \App\Enums\PlanType
        ? $invitation->plan->value
        : (string) $invitation->plan;

    expect($invitation)->not->toBeNull()
        ->and($invPlan)->toBe($proof->plan->value)
        ->and((float) $invitation->amount)->toBe((float) $proof->amount);
});

// ---------------------------------------------------------------------------
// T4 — Payment is created with Approved status
// ---------------------------------------------------------------------------

it('creates a Payment record with status APPROVED', function () {
    Mail::fake();

    $coach    = Admin::factory()->coach()->create();
    $reviewer = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $email    = 'pay-unit-' . uniqid() . '@wellcore.test';

    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create([
        'client_email' => $email,
    ]);

    (new ApprovePaymentProofAction)->handle($proof, $reviewer);

    $this->assertDatabaseHas('payments', [
        'email'  => $email,
        'status' => 'APPROVED',
    ]);
});

// ---------------------------------------------------------------------------
// T5 — Mail is queued (PaymentProofApproved sent to client_email)
// ---------------------------------------------------------------------------

it('queues a PaymentProofApproved mail to the client email', function () {
    Mail::fake();

    $coach    = Admin::factory()->coach()->create();
    $reviewer = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $email    = 'mail-unit-' . uniqid() . '@wellcore.test';

    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create([
        'client_email' => $email,
    ]);

    (new ApprovePaymentProofAction)->handle($proof, $reviewer);

    Mail::assertQueued(PaymentProofApproved::class, function ($mail) use ($email) {
        return $mail->hasTo($email);
    });
});

// ---------------------------------------------------------------------------
// T6 — In-app notification is created for the coach (user_id = coach_id)
// ---------------------------------------------------------------------------

it('creates a WellcoreNotification for the coach after approval', function () {
    Mail::fake();

    $coach    = Admin::factory()->coach()->create();
    $reviewer = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $proof    = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    (new ApprovePaymentProofAction)->handle($proof, $reviewer);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $coach->id,
        'type'    => 'payment_proof_approved',
    ]);
});

// ---------------------------------------------------------------------------
// T7 — proof.coach_invitation_id and proof.payment_id are set after approval
// ---------------------------------------------------------------------------

it('links coach_invitation_id and payment_id back to the proof after approval', function () {
    Mail::fake();

    $coach    = Admin::factory()->coach()->create();
    $reviewer = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $email    = 'link-unit-' . uniqid() . '@wellcore.test';

    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create([
        'client_email' => $email,
    ]);

    (new ApprovePaymentProofAction)->handle($proof, $reviewer);

    $proof->refresh();
    expect($proof->coach_invitation_id)->not->toBeNull()
        ->and($proof->payment_id)->not->toBeNull();

    // Verify the IDs actually point to real records
    expect(CoachInvitation::find($proof->coach_invitation_id))->not->toBeNull()
        ->and(Payment::find($proof->payment_id))->not->toBeNull();
});

// ---------------------------------------------------------------------------
// T8 — ClientCoach record has source='payment_proof' and active=true
// ---------------------------------------------------------------------------

it('creates ClientCoach with source=payment_proof and active=true', function () {
    Mail::fake();

    $coach    = Admin::factory()->coach()->create();
    $reviewer = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $email    = 'cc-unit-' . uniqid() . '@wellcore.test';

    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create([
        'client_email' => $email,
    ]);

    (new ApprovePaymentProofAction)->handle($proof, $reviewer);

    $client = \App\Models\Client::where('email', $email)->firstOrFail();

    $this->assertDatabaseHas('client_coach', [
        'client_id' => $client->id,
        'admin_id'  => $coach->id,
        'source'    => 'payment_proof',
        'active'    => 1,
    ]);
});
