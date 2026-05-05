<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for resolving the set of client IDs a coach can act on.
 *
 * Unions six signals so a client linked to a coach by ANY of them is included:
 *   1. client_coach pivot (primary, with active=true)
 *   2. clients.coach_id legacy FK
 *   3. assigned_plans.assigned_by
 *   4. coach_messages.coach_id
 *   5. coach_notes.coach_id
 *   6. plan_tickets.coach_id
 *
 * Used by CoachController (REST API) and the Livewire coach dashboards so both
 * surfaces see the same scope and IDOR boundaries stay consistent.
 */
final class CoachScope
{
    public static function clientIdsFor(int $coachId): Collection
    {
        $fromClientCoach = DB::table('client_coach')
            ->where('admin_id', $coachId)
            ->where('active', true)
            ->pluck('client_id');

        $fromClientsFk = DB::table('clients')
            ->where('coach_id', $coachId)
            ->pluck('id');

        $fromPlans = DB::table('assigned_plans')
            ->where('assigned_by', $coachId)
            ->pluck('client_id');

        $fromMessages = DB::table('coach_messages')
            ->where('coach_id', $coachId)
            ->pluck('client_id');

        $fromNotes = DB::table('coach_notes')
            ->where('coach_id', $coachId)
            ->pluck('client_id');

        $fromTickets = DB::table('plan_tickets')
            ->where('coach_id', $coachId)
            ->pluck('client_id');

        return $fromClientCoach
            ->concat($fromClientsFk)
            ->concat($fromPlans)
            ->concat($fromMessages)
            ->concat($fromNotes)
            ->concat($fromTickets)
            ->filter()
            ->unique()
            ->values();
    }
}
