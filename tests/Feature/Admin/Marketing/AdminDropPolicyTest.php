<?php
declare(strict_types=1);
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachContentDrop;
use App\Policies\Admin\Marketing\AdminDropPolicy;

it('superadmin can view any drop', function () {
    $superadmin = Admin::factory()->create(['role' => UserRole::Superadmin->value]);
    $drop = CoachContentDrop::factory()->create();
    expect((new AdminDropPolicy())->view($superadmin, $drop))->toBeTrue();
});

it('admin can approve drop', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin->value]);
    $drop = CoachContentDrop::factory()->inReview()->create();
    expect((new AdminDropPolicy())->approve($admin, $drop))->toBeTrue();
});

it('coach role cannot use AdminDropPolicy', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach->value]);
    $drop = CoachContentDrop::factory()->create();
    expect((new AdminDropPolicy())->view($coach, $drop))->toBeFalse();
});
