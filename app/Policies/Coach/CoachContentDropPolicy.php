<?php
declare(strict_types=1);
namespace App\Policies\Coach;

use App\Enums\Marketing\DropStatus;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachContentDrop;

final class CoachContentDropPolicy
{
    public function view(Admin $user, CoachContentDrop $drop): bool
    {
        return $user->role === UserRole::Coach
            && $drop->coach_id === $user->id
            && $drop->status->isVisibleToCoach();
    }

    public function markPiecePublished(Admin $user, CoachContentDrop $drop): bool
    {
        return $this->view($user, $drop)
            && in_array($drop->status, [DropStatus::Ready, DropStatus::InProgress], strict: true);
    }
}
