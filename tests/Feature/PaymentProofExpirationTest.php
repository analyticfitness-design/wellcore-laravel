<?php

/**
 * Artisan command: wellcore:expire-payment-proofs
 *
 * Marks payment_proofs with status=pendiente AND expires_at < now() as expirado.
 * Proofs that are already reviewed or not yet past their expires_at are untouched.
 */

use App\Enums\PaymentProofStatus;
use App\Models\Admin;
use App\Models\PaymentProof;

// ---------------------------------------------------------------------------
// T1 — Pending proof past its expires_at is marked expirado
// ---------------------------------------------------------------------------

it('marks pending proof with expires_at in the past as expirado', function () {
    $coach = Admin::factory()->coach()->create();

    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create([
        'expires_at' => now()->subHour(),
    ]);

    $this->artisan('wellcore:expire-payment-proofs')->assertExitCode(0);

    $proof->refresh();
    expect($proof->status)->toBe(PaymentProofStatus::Expirado);
});

// ---------------------------------------------------------------------------
// T2 — Pending proof with expires_at in the future is NOT touched
// ---------------------------------------------------------------------------

it('does not touch a pending proof whose expires_at is in the future', function () {
    $coach = Admin::factory()->coach()->create();

    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create([
        'expires_at' => now()->addDays(3),
    ]);

    $this->artisan('wellcore:expire-payment-proofs')->assertExitCode(0);

    $proof->refresh();
    expect($proof->status)->toBe(PaymentProofStatus::Pendiente);
});

// ---------------------------------------------------------------------------
// T3 — Already-approved proof with past expires_at is NOT touched
// ---------------------------------------------------------------------------

it('does not touch an approved proof even if expires_at is in the past', function () {
    $coach = Admin::factory()->coach()->create();

    $proof = PaymentProof::factory()->aprobado()->forCoach($coach)->create([
        'expires_at' => now()->subDay(),
    ]);

    $this->artisan('wellcore:expire-payment-proofs')->assertExitCode(0);

    $proof->refresh();
    expect($proof->status)->toBe(PaymentProofStatus::Aprobado);
});

// ---------------------------------------------------------------------------
// T4 — Already-rejected proof with past expires_at is NOT touched
// ---------------------------------------------------------------------------

it('does not touch a rejected proof even if expires_at is in the past', function () {
    $coach = Admin::factory()->coach()->create();

    $proof = PaymentProof::factory()->rechazado()->forCoach($coach)->create([
        'expires_at' => now()->subDay(),
    ]);

    $this->artisan('wellcore:expire-payment-proofs')->assertExitCode(0);

    $proof->refresh();
    expect($proof->status)->toBe(PaymentProofStatus::Rechazado);
});

// ---------------------------------------------------------------------------
// T5 — Command outputs the correct expired count
// ---------------------------------------------------------------------------

it('outputs the correct number of expired proofs', function () {
    $coach = Admin::factory()->coach()->create();

    // 3 proofs that should expire
    PaymentProof::factory()->pendiente()->forCoach($coach)->count(3)->create([
        'expires_at' => now()->subMinutes(30),
    ]);

    // 2 proofs that should NOT expire
    PaymentProof::factory()->pendiente()->forCoach($coach)->count(2)->create([
        'expires_at' => now()->addDays(5),
    ]);

    $this->artisan('wellcore:expire-payment-proofs')
         ->expectsOutput('3 comprobante(s) expirado(s).')
         ->assertExitCode(0);
});

// ---------------------------------------------------------------------------
// T6 — When nothing expires, output is "0 comprobante(s) expirado(s)."
// ---------------------------------------------------------------------------

it('outputs zero when no proofs need expiring', function () {
    // No proofs created — or all are fresh / already reviewed
    $this->artisan('wellcore:expire-payment-proofs')
         ->expectsOutput('0 comprobante(s) expirado(s).')
         ->assertExitCode(0);
});

// ---------------------------------------------------------------------------
// T7 — Batch: 5 expired proofs all change status in one run
// ---------------------------------------------------------------------------

it('expires all pending past-due proofs in a single run', function () {
    $coach = Admin::factory()->coach()->create();

    $proofs = PaymentProof::factory()->pendiente()->forCoach($coach)->count(5)->create([
        'expires_at' => now()->subHours(2),
    ]);

    $this->artisan('wellcore:expire-payment-proofs')->assertExitCode(0);

    foreach ($proofs as $proof) {
        $proof->refresh();
        expect($proof->status)->toBe(PaymentProofStatus::Expirado);
    }
});
