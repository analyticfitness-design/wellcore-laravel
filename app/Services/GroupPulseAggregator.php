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
     * Minimum number of recent workouts that triggers the aggregate card
     * "X personas terminaron entrenamiento en la última hora". Below this
     * threshold each workout would render as an individual event (future task);
     * above it we collapse them into a single group card to avoid noise.
     */
    private const AGGREGATE_MIN_PEOPLE = 6;

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
    public function buildFeed(int $coachId, string $time = 'today', string $type = 'all'): array
    {
        $clientIds = Client::where('coach_id', $coachId)->pluck('id');

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
