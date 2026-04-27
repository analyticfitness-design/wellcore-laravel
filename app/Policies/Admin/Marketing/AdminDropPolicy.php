<?php
declare(strict_types=1);
namespace App\Policies\Admin\Marketing;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachContentDrop;

final class AdminDropPolicy
{
    private function isAdminOrSuperadmin(Admin $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Superadmin], strict: true);
    }

    public function view(Admin $user, CoachContentDrop $drop): bool
    {
        return $this->isAdminOrSuperadmin($user);
    }

    public function update(Admin $user, CoachContentDrop $drop): bool
    {
        return $this->isAdminOrSuperadmin($user);
    }

    public function approve(Admin $user, CoachContentDrop $drop): bool
    {
        return $this->isAdminOrSuperadmin($user);
    }

    public function requestRegenerate(Admin $user, CoachContentDrop $drop): bool
    {
        return $this->isAdminOrSuperadmin($user);
    }

    public function manageAssets(Admin $user, CoachContentDrop $drop): bool
    {
        return $this->isAdminOrSuperadmin($user);
    }
}
