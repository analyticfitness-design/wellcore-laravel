<?php

/**
 * IDOR / Authorization policy tests for PaymentProof.
 *
 * PaymentProofPolicy rules:
 *   view    → admin/superadmin/jefe see all; coach sees only own (coach_id match)
 *   viewAny → all Admin-portal roles can list; coach controller filters by coach_id
 *   create  → coach and above
 *   approve → admin/superadmin/jefe only
 *   reject  → admin/superadmin/jefe only
 *
 * Clients have no access at all — blocked at route/controller layer.
 */

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use App\Models\PaymentProof;
use Illuminate\Support\Facades\Mail;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function bearerFor(Admin|Client $user, string $type = 'admin'): Tests\TestCase
{
    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type'  => $type,
        'user_id'    => $user->id,
        'token'      => $token,
        'expires_at' => now()->addDay(),
        'created_at' => now(),
    ]);

    return test()->withHeaders(['Authorization' => "Bearer {$token}"]);
}

// ---------------------------------------------------------------------------
// T1 — Coach A cannot view a proof that belongs to Coach B (403)
// ---------------------------------------------------------------------------

it('coach A cannot view a proof belonging to coach B (IDOR prevention)', function () {
    $coachA = Admin::factory()->coach()->create();
    $coachB = Admin::factory()->coach()->create();

    config(['wellcore.coach_contract.enabled' => false]);

    $proof = PaymentProof::factory()->pendiente()->forCoach($coachB)->create();

    $response = bearerFor($coachA)
        ->getJson("/api/v/coach/payment-proofs/{$proof->id}");

    $response->assertStatus(403);
});

// ---------------------------------------------------------------------------
// T2 — Coach can view their own proof (200)
// ---------------------------------------------------------------------------

it('coach can view their own proof', function () {
    $coach = Admin::factory()->coach()->create();
    config(['wellcore.coach_contract.enabled' => false]);

    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    $response = bearerFor($coach)
        ->getJson("/api/v/coach/payment-proofs/{$proof->id}");

    $response->assertStatus(200)
             ->assertJsonPath('id', $proof->id);
});

// ---------------------------------------------------------------------------
// T3 — Coach index only returns the coach's own proofs (not other coaches')
// ---------------------------------------------------------------------------

it('coach index returns only proofs owned by that coach', function () {
    $coachA = Admin::factory()->coach()->create();
    $coachB = Admin::factory()->coach()->create();

    config(['wellcore.coach_contract.enabled' => false]);

    // 2 proofs for coachA, 3 for coachB
    PaymentProof::factory()->pendiente()->forCoach($coachA)->count(2)->create();
    PaymentProof::factory()->pendiente()->forCoach($coachB)->count(3)->create();

    $response = bearerFor($coachA)
        ->getJson('/api/v/coach/payment-proofs');

    $response->assertStatus(200);

    $data = $response->json('data');
    expect($data)->toHaveCount(2);

    // Every returned proof should belong to coachA — verify via DB ids
    $coachAIds = PaymentProof::where('coach_id', $coachA->id)->pluck('id')->toArray();
    foreach ($data as $item) {
        expect($item['id'])->toBeIn($coachAIds);
    }
});

// ---------------------------------------------------------------------------
// T4 — Admin can view a proof from any coach
// ---------------------------------------------------------------------------

it('admin can view payment proof belonging to any coach', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    $response = bearerFor($admin)
        ->getJson("/api/v/admin/payment-proofs");

    $response->assertStatus(200);
    // At minimum our proof appears in the listing
    $ids = collect($response->json('data'))->pluck('id');
    expect($ids->contains($proof->id))->toBeTrue();
});

// ---------------------------------------------------------------------------
// T5 — Client has no access to the coach payment-proof endpoints (401/403)
// ---------------------------------------------------------------------------

it('client cannot access the coach payment-proof index endpoint', function () {
    $client = Client::factory()->create();
    config(['wellcore.coach_contract.enabled' => false]);

    // The v/coach/* routes require role:coach,admin,superadmin,jefe
    // A client token carries user_type=client, so resolveCoachOrFail aborts 403
    $response = bearerFor($client, 'client')
        ->getJson('/api/v/coach/payment-proofs');

    // The controller calls resolveCoachOrFail which aborts 403 for non-admin users
    $response->assertStatus(403);
});

// ---------------------------------------------------------------------------
// T6 — Client cannot access admin payment-proof endpoint (403)
// ---------------------------------------------------------------------------

it('client cannot access the admin payment-proof listing endpoint', function () {
    $client = Client::factory()->create();

    $response = bearerFor($client, 'client')
        ->getJson('/api/v/admin/payment-proofs');

    // v/admin/* middleware: auth:wellcore resolves a client token, but role middleware
    // blocks because client is not admin/superadmin/jefe
    $response->assertStatus(403);
});

// ---------------------------------------------------------------------------
// T7 — Superadmin can view any proof
// ---------------------------------------------------------------------------

it('superadmin can view any payment proof in the admin listing', function () {
    $superadmin = Admin::factory()->superadmin()->create();
    $coach      = Admin::factory()->coach()->create();
    $proof      = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    $response = bearerFor($superadmin)
        ->getJson('/api/v/admin/payment-proofs');

    $response->assertStatus(200);
    $ids = collect($response->json('data'))->pluck('id');
    expect($ids->contains($proof->id))->toBeTrue();
});

// ---------------------------------------------------------------------------
// T8 — Jefe role can approve (admin-level policy)
// ---------------------------------------------------------------------------

it('jefe role can approve a pending proof', function () {
    Mail::fake();

    $jefe  = Admin::factory()->create(['role' => UserRole::Jefe->value]);
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    $response = bearerFor($jefe)
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/approve");

    $response->assertStatus(200);
});

// ---------------------------------------------------------------------------
// T9 — Unauthenticated request to coach endpoint returns 401
// ---------------------------------------------------------------------------

it('unauthenticated request to coach payment-proof show returns 401', function () {
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->pendiente()->forCoach($coach)->create();

    $response = $this->getJson("/api/v/coach/payment-proofs/{$proof->id}");

    $response->assertStatus(401);
});
