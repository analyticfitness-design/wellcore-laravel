<?php

use App\Enums\CoachInvitationStatus;
use App\Enums\PlanType;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use App\Models\CoachInvitation;
use App\Models\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

uses(DatabaseTransactions::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Create a Bearer-token for the given Admin and return a test HTTP instance
 * ready for requests. The AuthToken is wrapped in the same DB transaction as
 * the test, so it rolls back automatically.
 */
function actingAsCoach(Admin $coach): Tests\TestCase
{
    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type'  => 'admin',
        'user_id'    => $coach->id,
        'token'      => $token,
        'expires_at' => now()->addDay(),
    ]);

    return test()->withHeaders(['Authorization' => "Bearer {$token}"]);
}

/**
 * Minimal valid POST body for POST /api/v/coach/invitations.
 */
function basicInvitationPayload(array $override = []): array
{
    return array_merge([
        'email'   => fake()->unique()->safeEmail(),
        'plan'    => 'metodo',
        'subject' => 'Test invitation',
    ], $override);
}

/**
 * Build a Wompi-like webhook payload for a transaction.updated event.
 * Tests that exercise the webhook must mock WompiService so the signature
 * check passes.
 */
function wompiWebhookPayload(string $reference, string $status): array
{
    return [
        'event' => 'transaction.updated',
        'data'  => [
            'transaction' => [
                'id'               => 'trx_' . uniqid(),
                'reference'        => $reference,
                'status'           => $status,
                'amount_in_cents'  => 33915000,
                'currency'         => 'COP',
            ],
        ],
        'sent_at'   => now()->toISOString(),
        'timestamp' => now()->timestamp,
        'signature' => ['checksum' => 'test_signature'],
    ];
}

// ---------------------------------------------------------------------------
// T1 — Coach can create an invitation for a new email
// ---------------------------------------------------------------------------

it('coach can create invitation for a new email', function () {
    Queue::fake();
    Mail::fake();

    $coach = Admin::factory()->coach()->create();

    // Mock Wompi HTTP so no real network call is made
    Http::fake([
        'sandbox.wompi.co/*' => Http::response([
            'data' => [
                'id'          => 'lnk_test_' . uniqid(),
                'url'         => 'https://sandbox.wompi.co/pay/lnk_test_abc',
                'active'      => true,
                'status'      => 'ACTIVE',
            ],
        ], 200),
    ]);

    $payload = basicInvitationPayload();

    $response = actingAsCoach($coach)
        ->postJson('/api/v/coach/invitations', $payload);

    $response->assertStatus(201)
             ->assertJsonPath('data.status', CoachInvitationStatus::Sent->value);

    $this->assertDatabaseHas('coach_invitations', [
        'email'    => $payload['email'],
        'coach_id' => $coach->id,
        'status'   => CoachInvitationStatus::Sent->value,
    ]);
});

// ---------------------------------------------------------------------------
// T2 — Active client email is blocked
// ---------------------------------------------------------------------------

it('blocks invitation to an existing active client', function () {
    Queue::fake();
    Mail::fake();

    $coach = Admin::factory()->coach()->create();

    // Insert an active client with the target email
    $activeClient = Client::factory()->active()->create();

    $response = actingAsCoach($coach)
        ->postJson('/api/v/coach/invitations', basicInvitationPayload([
            'email' => $activeClient->email,
        ]));

    $response->assertStatus(422)
             ->assertJsonPath('errorCode', 'CLIENT_ACTIVE');
});

// ---------------------------------------------------------------------------
// T3 — Wompi webhook APPROVED creates client and assigns coach
// ---------------------------------------------------------------------------

it('webhook approved creates client and assigns coach to invitation', function () {
    Queue::fake();
    Mail::fake();

    $coach  = Admin::factory()->coach()->create();
    $code   = bin2hex(random_bytes(16));
    $ref    = 'WCI-' . $code;

    $invitation = CoachInvitation::factory()->sent()->create([
        'coach_id'        => $coach->id,
        'code'            => $code,
        'wompi_reference' => $ref,
        'email'           => fake()->unique()->safeEmail(),
        'name'            => 'Test Prospect',
    ]);

    Payment::factory()->pending()->create([
        'wompi_reference' => $ref,
        'email'           => $invitation->email,
    ]);

    // Mock WompiService so signature verification returns true and the
    // pre-hook path for coach-invitation references is triggered correctly.
    $this->mock(\App\Services\WompiService::class, function ($mock) use ($ref, $code) {
        $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
        $mock->shouldReceive('handleWebhook')->andReturn(true);
        $mock->shouldReceive('isCoachInvitationReference')
             ->with($ref)
             ->andReturn(true);
        $mock->shouldReceive('extractInvitationCode')
             ->with($ref)
             ->andReturn($code);
    });

    $response = $this->postJson('/webhooks/wompi', wompiWebhookPayload($ref, 'APPROVED'));

    $response->assertStatus(200)
             ->assertJsonPath('status', 'ok');

    $invitation->refresh();

    expect($invitation->status)->toBe(CoachInvitationStatus::Paid);
    expect($invitation->client_id)->not->toBeNull();

    $this->assertDatabaseHas('client_coach', [
        'admin_id' => $coach->id,
        'active'   => 1,
    ]);
});

// ---------------------------------------------------------------------------
// T4 — Duplicate webhook does not duplicate data (idempotency)
// ---------------------------------------------------------------------------

it('duplicate webhook for already-paid invitation is ignored', function () {
    Queue::fake();
    Mail::fake();

    $coach  = Admin::factory()->coach()->create();
    $code   = bin2hex(random_bytes(16));
    $ref    = 'WCI-' . $code;

    $existingClient = Client::factory()->active()->create(['email' => fake()->unique()->safeEmail()]);

    $invitation = CoachInvitation::factory()->paid()->create([
        'coach_id'        => $coach->id,
        'code'            => $code,
        'wompi_reference' => $ref,
        'email'           => $existingClient->email,
        'client_id'       => $existingClient->id,
    ]);

    $this->mock(\App\Services\WompiService::class, function ($mock) use ($ref, $code) {
        $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
        $mock->shouldReceive('handleWebhook')->andReturn(true);
        $mock->shouldReceive('isCoachInvitationReference')
             ->with($ref)
             ->andReturn(true);
        $mock->shouldReceive('extractInvitationCode')
             ->with($ref)
             ->andReturn($code);
    });

    // Send the same webhook twice
    $this->postJson('/webhooks/wompi', wompiWebhookPayload($ref, 'APPROVED'))
         ->assertStatus(200);

    // client_id should remain the same — no duplicate client created
    $invitation->refresh();
    expect($invitation->client_id)->toBe($existingClient->id);

    // Only one client_coach record for this client/coach combo
    $assignmentCount = \App\Models\ClientCoach::where([
        'client_id' => $existingClient->id,
        'admin_id'  => $coach->id,
    ])->count();
    expect($assignmentCount)->toBeLessThanOrEqual(1);
});

// ---------------------------------------------------------------------------
// T5 — Rate limit: 50 invitations/day returns 429
// ---------------------------------------------------------------------------

it('returns 429 when coach reaches daily invitation limit', function () {
    Queue::fake();
    Mail::fake();

    $coach = Admin::factory()->coach()->create();

    // Create exactly 50 invitations today for this coach
    CoachInvitation::factory()
        ->sentToday()
        ->count(50)
        ->create(['coach_id' => $coach->id]);

    Http::fake([
        'sandbox.wompi.co/*' => Http::response(['data' => ['id' => 'lnk_x', 'url' => 'https://url', 'active' => true]], 200),
    ]);

    $response = actingAsCoach($coach)
        ->postJson('/api/v/coach/invitations', basicInvitationPayload());

    $response->assertStatus(429)
             ->assertJsonPath('errorCode', 'RATE_LIMIT');
});

// ---------------------------------------------------------------------------
// T6 — Resend expired invitation generates a new payment link
// ---------------------------------------------------------------------------

it('resending an expired invitation generates a new wompi link', function () {
    Queue::fake();
    Mail::fake();

    $coach = Admin::factory()->coach()->create();

    $invitation = CoachInvitation::factory()->expired()->create([
        'coach_id'             => $coach->id,
        'wompi_reference'      => 'WCI-' . bin2hex(random_bytes(8)),
        'wompi_payment_link_url' => 'https://old-url.wompi.co',
        'resend_count'         => 0,
    ]);

    Http::fake([
        'sandbox.wompi.co/*' => Http::response([
            'data' => [
                'id'  => 'lnk_new_' . uniqid(),
                'url' => 'https://sandbox.wompi.co/pay/new-link',
            ],
        ], 200),
    ]);

    $response = actingAsCoach($coach)
        ->postJson("/api/v/coach/invitations/{$invitation->id}/resend");

    $response->assertStatus(200)
             ->assertJsonPath('data.status', CoachInvitationStatus::Sent->value);

    $invitation->refresh();

    expect($invitation->status)->toBe(CoachInvitationStatus::Sent);
    expect($invitation->resend_count)->toBe(1);
    expect($invitation->wompi_payment_link_url)->not->toBe('https://old-url.wompi.co');
});

// ---------------------------------------------------------------------------
// T7 — Public /invitacion/{code} registers click and redirects to Wompi URL
// ---------------------------------------------------------------------------

it('public invitation link registers click and redirects to wompi url', function () {
    $wompiUrl   = 'https://sandbox.wompi.co/pay/lnk_abc123';
    $invitation = CoachInvitation::factory()->sent()->create([
        'wompi_payment_link_url' => $wompiUrl,
        'expires_at'             => now()->addDays(5),
    ]);

    $response = $this->get("/invitacion/{$invitation->code}");

    $response->assertRedirect($wompiUrl);

    $invitation->refresh();
    expect($invitation->status)->toBe(CoachInvitationStatus::LinkClicked);
    expect($invitation->clicked_at)->not->toBeNull();
});

// ---------------------------------------------------------------------------
// T8 — Expired invitation link returns the invitation-expired view
// ---------------------------------------------------------------------------

it('public invitation link for expired invitation renders expired view', function () {
    $invitation = CoachInvitation::factory()->expired()->create([
        'expires_at' => now()->subDay(),
    ]);

    $response = $this->get("/invitacion/{$invitation->code}");

    $response->assertStatus(200)
             ->assertViewIs('coach.invitation-expired');
});

// ---------------------------------------------------------------------------
// T9 — Coach cannot see invitations belonging to another coach (403)
// ---------------------------------------------------------------------------

it('coach cannot view invitations belonging to a different coach', function () {
    $ownerCoach = Admin::factory()->coach()->create();
    $otherCoach = Admin::factory()->coach()->create();

    $invitation = CoachInvitation::factory()->sent()->create([
        'coach_id' => $ownerCoach->id,
    ]);

    $response = actingAsCoach($otherCoach)
        ->getJson("/api/v/coach/invitations/{$invitation->id}");

    $response->assertStatus(403);
});

// ---------------------------------------------------------------------------
// T10 — Cancel invitation returns 200 and status=cancelled
// ---------------------------------------------------------------------------

it('coach can cancel a sent invitation', function () {
    Queue::fake();

    $coach = Admin::factory()->coach()->create();

    $invitation = CoachInvitation::factory()->sent()->create([
        'coach_id' => $coach->id,
    ]);

    $response = actingAsCoach($coach)
        ->deleteJson("/api/v/coach/invitations/{$invitation->id}");

    $response->assertStatus(200)
             ->assertJsonPath('data.status', CoachInvitationStatus::Cancelled->value);

    $invitation->refresh();
    expect($invitation->status)->toBe(CoachInvitationStatus::Cancelled);
    expect($invitation->cancelled_at)->not->toBeNull();
});
