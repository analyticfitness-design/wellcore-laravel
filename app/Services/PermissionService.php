<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    public static function hasPermission(string $role, string $permission): bool
    {
        $permissions = Cache::remember(
            "permissions:{$role}",
            now()->addHours(1),
            fn () => DB::table('role_permissions')
                ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->where('role_permissions.role', $role)
                ->pluck('permissions.name')
                ->toArray()
        );

        return in_array($permission, $permissions);
    }

    public static function clearCache(string $role): void
    {
        Cache::forget("permissions:{$role}");
    }
}
