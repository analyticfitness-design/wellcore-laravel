<?php
declare(strict_types=1);
namespace App\Policies\Coach;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachMarketingProfile;

final class CoachMarketingProfilePolicy
{
    public function view(Admin $user, CoachMarketingProfile $profile): bool
    {
        return $user->role === UserRole::Coach && $profile->coach_id === $user->id;
    }

    public function update(Admin $user, CoachMarketingProfile $profile): bool
    {
        return $this->view($user, $profile);
    }
}
