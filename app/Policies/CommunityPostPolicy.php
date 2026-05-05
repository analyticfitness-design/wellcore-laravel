<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\CommunityPost;

class CommunityPostPolicy
{
    public function canModerate(Admin $actor, CommunityPost $post): bool
    {
        $role = $this->roleValue($actor);

        if (in_array($role, ['admin', 'superadmin', 'jefe'], true)) {
            return true;
        }

        if ($role === 'coach' && (int) $post->coach_admin_id === (int) $actor->id) {
            return true;
        }

        return false;
    }

    public function canPin(Admin $actor, CommunityPost $post): bool
    {
        return $this->canModerate($actor, $post);
    }

    public function canMakeOfficial(Admin $actor, CommunityPost $post): bool
    {
        return $this->canModerate($actor, $post);
    }

    public function canDelete(Admin $actor, CommunityPost $post): bool
    {
        return $this->canModerate($actor, $post);
    }

    public function canCreateGlobalOfficial(Admin $actor): bool
    {
        return in_array($this->roleValue($actor), ['admin', 'superadmin', 'jefe'], true);
    }

    private function roleValue(Admin $actor): string
    {
        $role = $actor->role;

        return $role instanceof \BackedEnum ? $role->value : (string) $role;
    }
}
