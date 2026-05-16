<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Client;

class ClientPolicy
{
    public function view(Client|Admin $user, Client $client): bool
    {
        if ($user instanceof Client) {
            return $user->id === $client->id;
        }

        if ($user instanceof Admin && $user->role?->value === 'coach') {
            if (\Illuminate\Support\Facades\Schema::hasColumn('clients', 'coach_id')
                && $client->coach_id === $user->id) {
                return true;
            }
            return \App\Models\AssignedPlan::where('assigned_by', $user->id)
                ->where('client_id', $client->id)
                ->exists();
        }

        return in_array($user->role?->value, ['admin', 'superadmin', 'jefe']);
    }

    public function update(Client|Admin $user, Client $client): bool
    {
        if ($user instanceof Client) {
            return $user->id === $client->id;
        }

        return in_array($user->role?->value, ['admin', 'superadmin', 'jefe']);
    }

    /**
     * Autoriza la extensión manual de membresía.
     *
     * - Admin/Superadmin/Jefe: pueden extender cualquier cliente
     * - Coach: solo sus propios clientes (3 fuentes en orden de preferencia)
     *   1. Pivot `client_coach` con active=true (fuente moderna)
     *   2. Columna legacy `clients.coach_id`
     *   3. Coach que asignó al menos un AssignedPlan activo al cliente
     */
    public function extendMembership(Admin $actor, Client $client): bool
    {
        $role = $actor->role?->value;

        if (in_array($role, ['admin', 'superadmin', 'jefe'], true)) {
            return true;
        }

        if ($role !== 'coach') {
            return false;
        }

        $pivotActive = \Illuminate\Support\Facades\DB::table('client_coach')
            ->where('client_id', $client->id)
            ->where('admin_id', $actor->id)
            ->where('active', true)
            ->exists();

        if ($pivotActive) {
            return true;
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('clients', 'coach_id')
            && (int) $client->coach_id === (int) $actor->id) {
            return true;
        }

        return \App\Models\AssignedPlan::query()
            ->where('client_id', $client->id)
            ->where('assigned_by', $actor->id)
            ->where('active', true)
            ->exists();
    }
}
