<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Client;
use App\Models\PlanTicket;

class PlanTicketPolicy
{
    public function view(Client|Admin $user, PlanTicket $ticket): bool
    {
        if ($user instanceof Client) {
            return $ticket->client_id === $user->id;
        }

        if ($user instanceof Admin && $user->role?->value === 'coach') {
            return $ticket->coach_id === $user->id;
        }

        return in_array($user->role?->value, ['admin', 'superadmin', 'jefe']);
    }

    public function update(Client|Admin $user, PlanTicket $ticket): bool
    {
        if ($user instanceof Client) {
            return false;
        }

        if ($user instanceof Admin && $user->role?->value === 'coach') {
            return $ticket->coach_id === $user->id;
        }

        return in_array($user->role?->value, ['admin', 'superadmin', 'jefe']);
    }
}
