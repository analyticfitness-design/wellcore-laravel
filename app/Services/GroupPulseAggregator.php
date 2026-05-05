<?php

namespace App\Services;

use App\Models\Client;
use App\Models\PersonalRecord;
use App\Models\WorkoutSession;
use App\Scopes\OwnedByClientScope;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class GroupPulseAggregator
{
    /**
     * Aggregated counters for the "Latido del Grupo" feed of a single coach.
     *
     * The "group" in WellCore is implicitly defined by clients.coach_id —
     * never cross-coach. Returns zero-valued counters when the coach has no
     * clients, so callers can render the empty state without null checks.
     *
     * @return array{workouts_today:int, prs_week:int, achievements_today:int, checkins_week:int}
     */
    public function computeStats(int $coachId): array
    {
        $clientIds = Client::where('coach_id', $coachId)->pluck('id');

        if ($clientIds->isEmpty()) {
            return $this->zeroStats();
        }

        return [
            'workouts_today' => $this->countWorkoutsToday($clientIds),
            'prs_week' => $this->countPrsThisWeek($clientIds),
            'achievements_today' => $this->countAchievementsToday($clientIds),
            'checkins_week' => $this->countCheckinsThisWeek($clientIds),
        ];
    }

    /**
     * @return array{workouts_today:int, prs_week:int, achievements_today:int, checkins_week:int}
     */
    private function zeroStats(): array
    {
        return [
            'workouts_today' => 0,
            'prs_week' => 0,
            'achievements_today' => 0,
            'checkins_week' => 0,
        ];
    }

    private function countWorkoutsToday(Collection $clientIds): int
    {
        // Bypass OwnedByClientScope: aggregator runs in client-authenticated
        // context, but we need to count across the entire coach's group, not
        // just the auth user's own sessions.
        return WorkoutSession::withoutGlobalScope(OwnedByClientScope::class)
            ->whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->whereDate('session_date', Carbon::today())
            ->count();
    }

    private function countPrsThisWeek(Collection $clientIds): int
    {
        return PersonalRecord::whereIn('client_id', $clientIds)
            ->where('is_current', 1)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();
    }

    private function countAchievementsToday(Collection $clientIds): int
    {
        return DB::table('client_achievements')
            ->whereIn('client_id', $clientIds)
            ->whereDate('created_at', Carbon::today())
            ->count();
    }

    private function countCheckinsThisWeek(Collection $clientIds): int
    {
        // checkins lives only in vanilla prod DB; tests/local return 0.
        if (! Schema::hasTable('checkins')) {
            return 0;
        }

        return DB::table('checkins')
            ->whereIn('client_id', $clientIds)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();
    }
}
