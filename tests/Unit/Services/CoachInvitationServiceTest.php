<?php

use App\Enums\CoachInvitationStatus;
use App\Models\Admin;
use App\Models\CoachInvitation;
use App\Services\CoachInvitationService;
use App\Services\WompiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

// ---------------------------------------------------------------------------
// T-U1 — CoachInvitationStatus::isTerminal() logic
// ---------------------------------------------------------------------------

it('paid and cancelled statuses are terminal', function () {
    expect(CoachInvitationStatus::Paid->isTerminal())->toBeTrue();
    expect(CoachInvitationStatus::Cancelled->isTerminal())->toBeTrue();
});

it('sent, opened, link_clicked, expired and failed statuses are not terminal', function () {
    expect(CoachInvitationStatus::Sent->isTerminal())->toBeFalse();
    expect(CoachInvitationStatus::Opened->isTerminal())->toBeFalse();
    expect(CoachInvitationStatus::LinkClicked->isTerminal())->toBeFalse();
    expect(CoachInvitationStatus::Expired->isTerminal())->toBeFalse();
    expect(CoachInvitationStatus::Failed->isTerminal())->toBeFalse();
});

// ---------------------------------------------------------------------------
// T-U2 — expireOverdue() updates only non-terminal overdue invitations
// ---------------------------------------------------------------------------

it('expireOverdue updates only non-terminal past-due invitations', function () {
    $coach = Admin::factory()->coach()->create();

    // Should be expired by the job: sent + past expires_at
    $overdueInvitation = CoachInvitation::factory()->sent()->pastDue()->create([
        'coach_id' => $coach->id,
    ]);

    // Should NOT be touched: already paid (terminal)
    $paidInvitation = CoachInvitation::factory()->paid()->create([
        'coach_id'   => $coach->id,
        'expires_at' => now()->subDays(2),
    ]);

    // Should NOT be touched: future expiry
    $validInvitation = CoachInvitation::factory()->sent()->create([
        'coach_id'   => $coach->id,
        'expires_at' => now()->addDays(3),
    ]);

    $wompiMock = Mockery::mock(WompiService::class);
    $service   = new CoachInvitationService($wompiMock);

    $updated = $service->expireOverdue();

    expect($updated)->toBeGreaterThanOrEqual(1);

    $overdueInvitation->refresh();
    expect($overdueInvitation->status)->toBe(CoachInvitationStatus::Expired);

    $paidInvitation->refresh();
    expect($paidInvitation->status)->toBe(CoachInvitationStatus::Paid);

    $validInvitation->refresh();
    expect($validInvitation->status)->toBe(CoachInvitationStatus::Sent);
});

// ---------------------------------------------------------------------------
// T-U3 — enforceRateLimit throws when coach hits 50 invitations today
// ---------------------------------------------------------------------------

it('enforceRateLimit throws CoachInvitationRateLimitException when 50 invitations exist today', function () {
    $coach = Admin::factory()->coach()->create();

    // Create 50 invitations for today
    CoachInvitation::factory()
        ->sentToday()
        ->count(50)
        ->create(['coach_id' => $coach->id]);

    $wompiMock = Mockery::mock(WompiService::class);
    $service   = new CoachInvitationService($wompiMock);

    // Use reflection to call the private method
    $reflector = new ReflectionClass($service);
    $method    = $reflector->getMethod('enforceRateLimit');
    $method->setAccessible(true);

    expect(fn () => $method->invoke($service, $coach))
        ->toThrow(\App\Exceptions\CoachInvitationRateLimitException::class);
});
