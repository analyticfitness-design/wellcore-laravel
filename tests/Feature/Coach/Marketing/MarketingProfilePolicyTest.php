<?php
declare(strict_types=1);
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachMarketingProfile;
use App\Policies\Coach\CoachMarketingProfilePolicy;

it('coach can view own profile', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $profile = CoachMarketingProfile::factory()->create(['coach_id' => $coach->id]);
    expect((new CoachMarketingProfilePolicy())->view($coach, $profile))->toBeTrue();
});

it('coach CANNOT view another coachs profile — IDOR', function () {
    $coachA = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $coachB = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $profile = CoachMarketingProfile::factory()->create(['coach_id' => $coachB->id]);
    expect((new CoachMarketingProfilePolicy())->view($coachA, $profile))->toBeFalse();
});
