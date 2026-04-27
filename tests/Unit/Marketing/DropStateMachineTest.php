<?php

declare(strict_types=1);

use App\Enums\Marketing\DropStatus;
use App\Enums\UserRole;
use App\Exceptions\Marketing\InvalidDropTransition;
use App\Models\Admin;
use App\Models\CoachContentDrop;
use App\Services\Marketing\DropStateMachine;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('allows pending -> generating', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $drop  = CoachContentDrop::factory()->pending()->create();

    (new DropStateMachine())->transition($drop, DropStatus::Generating, $admin);

    expect($drop->fresh()->status)->toBe(DropStatus::Generating);
});

it('rejects pending -> completed (invalid transition)', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $drop  = CoachContentDrop::factory()->pending()->create();

    expect(fn () => (new DropStateMachine())->transition($drop, DropStatus::Completed, $admin))
        ->toThrow(InvalidDropTransition::class);
});

it('records approved_at and approved_by_id at in_review -> approved', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $drop  = CoachContentDrop::factory()->inReview()->create();

    (new DropStateMachine())->transition($drop, DropStatus::Approved, $admin);

    $drop->refresh();

    expect($drop->approved_at)->not->toBeNull()
        ->and($drop->approved_by_id)->toBe($admin->id);
});

it('canTransition returns false for invalid move', function () {
    $drop = CoachContentDrop::factory()->archived()->create();

    expect((new DropStateMachine())->canTransition($drop, DropStatus::Pending))->toBeFalse();
});
