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
}
