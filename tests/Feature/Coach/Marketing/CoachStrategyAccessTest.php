<?php
declare(strict_types=1);
use App\Enums\Marketing\DropStatus;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachContentDrop;
use App\Policies\Coach\CoachContentDropPolicy;

it('coach can view own ready drop', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $drop = CoachContentDrop::factory()->ready()->create(['coach_id' => $coach->id]);
    expect((new CoachContentDropPolicy())->view($coach, $drop))->toBeTrue();
});

it('coach CANNOT view another coachs drop — IDOR prevented', function () {
    $coachA = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $coachB = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $drop = CoachContentDrop::factory()->ready()->create(['coach_id' => $coachB->id]);
    expect((new CoachContentDropPolicy())->view($coachA, $drop))->toBeFalse();
});

it('coach cannot view in_review drop (only ready+ visible)', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $drop = CoachContentDrop::factory()->inReview()->create(['coach_id' => $coach->id]);
    expect((new CoachContentDropPolicy())->view($coach, $drop))->toBeFalse();
});

it('non-coach role cannot use CoachContentDropPolicy', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $drop = CoachContentDrop::factory()->ready()->create();
    expect((new CoachContentDropPolicy())->view($admin, $drop))->toBeFalse();
});
