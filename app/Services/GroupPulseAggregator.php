<?php

namespace App\Services;

use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\CoachMessage;
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
     * Minimum number of recent workouts that triggers the aggregate card
     * "X personas terminaron entrenamiento en la última hora". Below this
     * threshold each workout would render as an individual event (future task);
     * above it we collapse them into a single group card to avoid noise.
     *
     * Audit fix 2026-05-05: bajado de 6 a 3 — grupos reales WellCore (~5
     * clientes/coach) jamás llegaban al threshold y la card era dead code.
     */
    private const AGGREGATE_MIN_PEOPLE = 3;

    /**
     * Aggregated counters for the "Latido del Grupo" feed of a single coach.
     *
     * The "group" in WellCore is implicitly defined by clients.coach_id —
     * never cross-coach. Returns zero-valued counters when the coach has no
     * clients, so callers can render the empty state without null checks.
     *
     * @return array{workouts_today:int, prs_week:int, achievements_today:int, checkins_week:int}
     */
    public function computeStats(int $coachId, ?Collection $preResolvedClientIds = null): array
    {
        $clientIds = $preResolvedClientIds ?? $this->resolveCoachClientIds($coachId);

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
     * Activity feed for the coach's group, sorted newest-first by minutes_ago.
     *
     * Mixes two event shapes:
     *  - PR events  (per-client) — respect clients.autoshare_pr opt-out.
     *  - Aggregate workout card — only emitted when 6+ clients completed a
     *    workout in the last hour (avoids individual-event noise on busy days).
     *    Respects clients.autoshare_workout opt-out.
     *
     * @param  string  $time  'today' | 'week' | 'all'
     * @param  string  $type  'all' | 'pr' | 'workout'  (extended in later tasks)
     * @return array<int, array<string, mixed>>
     */
    public function buildFeed(int $coachId, string $time = 'today', string $type = 'all', ?Collection $preResolvedClientIds = null): array
    {
        $clientIds = $preResolvedClientIds ?? $this->resolveCoachClientIds($coachId);

        if ($clientIds->isEmpty()) {
            return [];
        }

        $since = $this->resolveSince($time);
        $events = collect();

        if ($type === 'all' || $type === 'pr') {
            $events = $events->merge($this->prEvents($clientIds, $since));
        }

        if ($type === 'all' || $type === 'workout') {
            $aggregate = $this->aggregateRecentWorkouts($clientIds, $since);
            if ($aggregate !== null) {
                $events->push($aggregate);
            }
        }

        return $events
            ->sortBy('minutes_ago')
            ->values()
            ->all();
    }

    /**
     * Compare a single client's weekly activity against the rest of the
     * coach's group. Used by the dashboard "tu eres top X%" card.
     *
     * Always returns a populated shape (even with zeros) so callers don't need
     * null checks. missions_peers is reserved for a future task; today empty.
     *
     * @return array{weekly_workouts: array{user:int, group_avg:float, rank_pct:int}, missions_peers: array<int,int>}
     */
    public function userVsGroup(int $coachId, int $clientId, ?Collection $preResolvedClientIds = null): array
    {
        $clientIds = $preResolvedClientIds ?? $this->resolveCoachClientIds($coachId);

        if ($clientIds->isEmpty()) {
            return $this->emptyUserVsGroup();
        }

        $weekAgo = Carbon::now()->subDays(7);

        $userCount = (int) WorkoutSession::withoutGlobalScope(OwnedByClientScope::class)
            ->where('client_id', $clientId)
            ->where('completed', true)
            ->where('session_date', '>=', $weekAgo)
            ->count();

        $groupCounts = WorkoutSession::withoutGlobalScope(OwnedByClientScope::class)
            ->whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->where('session_date', '>=', $weekAgo)
            ->select('client_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('client_id')
            ->pluck('cnt')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $groupAvg = $groupCounts === []
            ? 0.0
            : round(array_sum($groupCounts) / count($groupCounts), 1);

        return [
            'weekly_workouts' => [
                'user' => $userCount,
                'group_avg' => (float) $groupAvg,
                'rank_pct' => $this->rankPercentile($userCount, $groupCounts),
            ],
            'missions_peers' => [],
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

    /**
     * @return array{weekly_workouts: array{user:int, group_avg:float, rank_pct:int}, missions_peers: array<int,int>}
     */
    private function emptyUserVsGroup(): array
    {
        return [
            'weekly_workouts' => [
                'user' => 0,
                'group_avg' => 0.0,
                'rank_pct' => 0,
            ],
            'missions_peers' => [],
        ];
    }

    /**
     * Top-percentile rank: percent of the group at or above $value.
     * Smaller is better — "top 10%" means $value is in the elite 10%.
     * Empty group → 0.
     *
     * @param  array<int,int>  $allValues
     */
    private function rankPercentile(int $value, array $allValues): int
    {
        if ($allValues === []) {
            return 0;
        }

        $strictlyBelow = count(array_filter($allValues, fn ($v) => $v < $value));

        return (int) round(100 * (1 - $strictlyBelow / count($allValues)));
    }

    /**
     * Subset de clientIds que tienen $flag activo. Para cada counter aplicamos
     * el flag correspondiente (audit fix 2026-05-05): antes los counters de
     * achievements/medal y checkins ignoraban el opt-out, contradiciendo la
     * promesa de privacidad granular.
     */
    private function clientIdsWithFlag(Collection $clientIds, string $flag): Collection
    {
        if ($clientIds->isEmpty()) {
            return collect();
        }

        return Client::whereIn('id', $clientIds)
            ->where($flag, 1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);
    }

    private function countWorkoutsToday(Collection $clientIds): int
    {
        $visible = $this->clientIdsWithFlag($clientIds, 'autoshare_workout');
        if ($visible->isEmpty()) {
            return 0;
        }

        // Bypass OwnedByClientScope: aggregator runs in client-authenticated
        // context, but we need to count across the entire coach's group, not
        // just the auth user's own sessions.
        return WorkoutSession::withoutGlobalScope(OwnedByClientScope::class)
            ->whereIn('client_id', $visible)
            ->where('completed', true)
            ->whereDate('session_date', Carbon::today())
            ->count();
    }

    private function countPrsThisWeek(Collection $clientIds): int
    {
        $visible = $this->clientIdsWithFlag($clientIds, 'autoshare_pr');
        if ($visible->isEmpty()) {
            return 0;
        }

        return PersonalRecord::whereIn('client_id', $visible)
            ->where('is_current', 1)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();
    }

    private function countAchievementsToday(Collection $clientIds): int
    {
        $visible = $this->clientIdsWithFlag($clientIds, 'autoshare_medal');
        if ($visible->isEmpty()) {
            return 0;
        }

        return DB::table('client_achievements')
            ->whereIn('client_id', $visible)
            ->whereDate('created_at', Carbon::today())
            ->count();
    }

    private function countCheckinsThisWeek(Collection $clientIds): int
    {
        // checkins lives only in vanilla prod DB; tests/local return 0.
        if (! Schema::hasTable('checkins')) {
            return 0;
        }

        // Check-ins son progreso íntimo — los respetamos solo si autoshare_streak
        // está on (semánticamente: si compartes tu racha, compartes que hiciste check-in).
        $visible = $this->clientIdsWithFlag($clientIds, 'autoshare_streak');
        if ($visible->isEmpty()) {
            return 0;
        }

        return DB::table('checkins')
            ->whereIn('client_id', $visible)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();
    }

    /**
     * Resolve the set of client_ids belonging to a coach using the same
     * 3-fallback rule as ClientController::myCoach (since clients.coach_id
     * is sparsely populated in production):
     *   1. clients.coach_id = $coachId
     *   2. assigned_plans.assigned_by = $coachId (latest per client)
     *   3. coach_messages.coach_id = $coachId (latest per client)
     *
     * Union of all three. Returns a Collection<int> of distinct client_ids.
     */
    /**
     * Resolve client_ids para un coach. Public porque el controller lo invoca
     * UNA vez antes de llamar las 3 funciones públicas — evita 3× la misma
     * resolución (3 queries) en cada hit de cache miss. Audit fix 2026-05-05.
     */
    public function resolveCoachClientIds(int $coachId): Collection
    {
        $ids = collect();

        if (Schema::hasColumn('clients', 'coach_id')) {
            $ids = $ids->merge(
                Client::where('coach_id', $coachId)->pluck('id')
            );
        }

        if (Schema::hasTable('assigned_plans')) {
            $ids = $ids->merge(
                AssignedPlan::where('assigned_by', $coachId)
                    ->whereNotNull('client_id')
                    ->distinct()
                    ->pluck('client_id')
            );
        }

        if (Schema::hasTable('coach_messages')) {
            $ids = $ids->merge(
                CoachMessage::where('coach_id', $coachId)
                    ->whereNotNull('client_id')
                    ->distinct()
                    ->pluck('client_id')
            );
        }

        return $ids->map(fn ($id) => (int) $id)->unique()->values();
    }

    private function resolveSince(string $time): Carbon
    {
        return match ($time) {
            'week' => Carbon::now()->subDays(7),
            'all' => Carbon::now()->subDays(30),
            default => Carbon::today(),
        };
    }

    /**
     * @return Collection<int, array{type:string, client_name:string, client_initials:string, headline:string, minutes_ago:int}>
     */
    private function prEvents(Collection $clientIds, Carbon $since): Collection
    {
        return PersonalRecord::whereIn('client_id', $clientIds)
            ->where('is_current', 1)
            ->where('created_at', '>=', $since)
            ->whereHas('client', fn ($q) => $q->where('autoshare_pr', 1))
            ->with('client:id,name')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (PersonalRecord $pr) => $this->prToEvent($pr));
    }

    /**
     * Returns null when fewer than AGGREGATE_MIN_PEOPLE clients trained in the
     * last hour. The window is intersected with $since so 'today' calls early
     * in the morning don't reach into yesterday.
     *
     * @return array{type:string, headline:string, people_count:int, preview_initials:array<int,string>, extra:string, minutes_ago:int}|null
     */
    private function aggregateRecentWorkouts(Collection $clientIds, Carbon $since): ?array
    {
        $hourAgo = Carbon::now()->subHour();
        $window = $since->greaterThan($hourAgo) ? $since : $hourAgo;

        $rows = WorkoutSession::withoutGlobalScope(OwnedByClientScope::class)
            ->whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->where('updated_at', '>=', $window)
            ->whereHas('client', fn ($q) => $q->where('autoshare_workout', 1))
            ->with('client:id,name')
            ->select('client_id', 'total_volume_kg')
            ->get();

        $count = $rows->count();

        if ($count < self::AGGREGATE_MIN_PEOPLE) {
            return null;
        }

        $totalVolume = (int) $rows->sum('total_volume_kg');
        $previewInitials = $rows->take(3)
            ->map(fn ($row) => $this->initials($row->client?->name ?? 'M'))
            ->all();

        if ($count > 3) {
            $previewInitials[] = '+'.($count - 3);
        }

        return [
            'type' => 'aggregate',
            'headline' => "{$count} personas terminaron entrenamiento en la última hora",
            'people_count' => $count,
            'preview_initials' => $previewInitials,
            'extra' => number_format($totalVolume).' kg movidos en total',
            'minutes_ago' => 0,
        ];
    }

    /**
     * @return array{type:string, client_name:string, client_initials:string, headline:string, minutes_ago:int}
     */
    private function prToEvent(PersonalRecord $pr): array
    {
        $clientName = $pr->client?->name ?? 'Miembro';

        return [
            'type' => 'pr',
            'client_name' => $this->shortName($clientName),
            'client_initials' => $this->initials($clientName),
            'headline' => $this->prHeadline($pr),
            'minutes_ago' => (int) $pr->created_at->diffInMinutes(Carbon::now()),
        ];
    }

    private function prHeadline(PersonalRecord $pr): string
    {
        $exercise = $pr->exercise ?? 'Ejercicio';
        $weight = $pr->weight ? "{$pr->weight}kg" : '';
        $reps = $pr->reps ? " x{$pr->reps}" : '';

        return trim("rompió PR de {$exercise} {$weight}{$reps}");
    }

    private function shortName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        if (count($parts) < 2) {
            return $parts[0] ?? 'Miembro';
        }

        return $parts[0].' '.mb_strtoupper(mb_substr($parts[1], 0, 1)).'.';
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = mb_substr($parts[0] ?? 'M', 0, 1);
        $second = mb_substr($parts[1] ?? '', 0, 1);

        return mb_strtoupper($first.$second);
    }
}
