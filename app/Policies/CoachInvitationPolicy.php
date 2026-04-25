<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachInvitation;

class CoachInvitationPolicy
{
    private function isAdminOrAbove(Admin $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Superadmin, UserRole::Jefe]);
    }

    public function viewAny(Admin $user): bool
    {
        return true; // Todos los roles con acceso al portal coach pueden listar (filtrado en query)
    }

    public function view(Admin $user, CoachInvitation $invitation): bool
    {
        return $this->isAdminOrAbove($user) || $invitation->coach_id === $user->id;
    }

    public function create(Admin $user): bool
    {
        return true; // Todos los roles del portal coach
    }

    public function cancel(Admin $user, CoachInvitation $invitation): bool
    {
        return $this->isAdminOrAbove($user) || $invitation->coach_id === $user->id;
    }

    public function resend(Admin $user, CoachInvitation $invitation): bool
    {
        return $this->isAdminOrAbove($user) || $invitation->coach_id === $user->id;
    }
}
