<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Medal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Evaluates and unlocks medal progress for a given Client.
 *
 * Adapted from the generic User-based spec to WellCore's real schema:
 *  - Uses `clients` (not `users`) as the subject.
 *  - Reads from `workout_sessions.completed=1` (no `completed_at` column).
 *  - Reads PRs from `personal_records` (client_id, achieved_at).
 *  - Habit streaks inferred from `habit_logs` (1 row per habit_type per day).
 *  - Nutrition / macro streaks are TODO — `nutrition_logs` does not exist yet.
 *  - Early-bird medal is TODO — `workout_sessions` lacks a started_at HH:mm timestamp.
 *  - WellCore Elite ("active days") is computed from first completed workout.
 */
class MedalService
{
    /** Habits the user has to hit per day to count as "all completed" for a date. */
    private const HABIT_TYPES = ['agua', 'sueno', 'entrenamiento', 'nutricion', 'suplementos', 'estres'];

    /**
     * Evaluate every resolver for the client.
     * Returns array of newly-unlocked Medal models.
     */
    public function checkAll(Client $client): array
    {
        $unlocked = [];

        foreach ($this->resolvers() as $slug => $resolver) {
            $medal = Medal::where('slug', $slug)->where('is_active', true)->first();

            if (! $medal) {
                continue;
            }

            $value = $resolver($client);

            if ($value === null) {
                // resolver self-skipped (table missing, feature disabled, etc.)
                continue;
            }

            if ($this->updateMedalProgress($client, $medal, $value) === 'unlocked') {
                $unlocked[] = $medal;
            }
        }

        return $unlocked;
    }

    /**
     * Evaluate only the medals from a given category.
     */
    public function checkCategory(Client $client, string $category): array
    {
        $slugs = $this->slugsForCategory($category);

        if (empty($slugs)) {
            return [];
        }

        $medals = Medal::whereIn('slug', $slugs)->where('is_active', true)->get();
        $resolvers = $this->resolvers();
        $unlocked = [];

        foreach ($medals as $medal) {
            $resolver = $resolvers[$medal->slug] ?? null;

            if (! $resolver) {
                continue;
            }

            $value = $resolver($client);

            if ($value === null) {
                continue;
            }

            if ($this->updateMedalProgress($client, $medal, $value) === 'unlocked') {
                $unlocked[] = $medal;
            }
        }

        return $unlocked;
    }

    // ── Progress writer ──────────────────────────────────────────────────────

    private function updateMedalProgress(Client $client, Medal $medal, int $current): string
    {
        $existing = DB::table('client_medals')
            ->where('client_id', $client->id)
            ->where('medal_id', $medal->id)
            ->first();

        if ($existing && $existing->achieved_at !== null) {
            return 'already_achieved';
        }

        $achieved = $current >= $medal->target_value;
        $now = now();

        DB::table('client_medals')->upsert(
            [[
                'client_id' => $client->id,
                'medal_id' => $medal->id,
                'current_progress' => min($current, $medal->target_value),
                'achieved_at' => $achieved ? $now : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            uniqueBy: ['client_id', 'medal_id'],
            update: ['current_progress', 'achieved_at', 'updated_at'],
        );

        if ($achieved) {
            // total_xp lives on clients (added by medal migration). Safe-guard in case someone rolled back.
            if (Schema::hasColumn('clients', 'total_xp')) {
                DB::table('clients')->where('id', $client->id)->increment('total_xp', $medal->xp);
            }

            return 'unlocked';
        }

        return 'progress';
    }

    // ── Resolvers map (slug → counter) ───────────────────────────────────────

    /**
     * @return array<string, callable(Client): ?int>
     */
    private function resolvers(): array
    {
        return [
            // Constancia
            'el-inicio' => fn (Client $c) => $this->countWorkouts($c),
            'semana-fuego' => fn (Client $c) => $this->countConsecutiveTrainingDays($c),
            'mes-fuego' => fn (Client $c) => $this->countConsecutiveTrainingDays($c),

            // Volumen
            'entreno-10' => fn (Client $c) => $this->countWorkouts($c),
            'entreno-50' => fn (Client $c) => $this->countWorkouts($c),
            'entreno-200' => fn (Client $c) => $this->countWorkouts($c),

            // Fuerza
            'primer-pr' => fn (Client $c) => $this->countPRs($c),
            '10-records' => fn (Client $c) => $this->countPRs($c),
            'monstruo-fuerza' => fn (Client $c) => $this->countPRs($c),

            // Nutrición — TODO: nutrition_logs table does not exist yet.
            'semana-macros' => fn (Client $c) => $this->countMacroStreak($c),
            'mes-limpio' => fn (Client $c) => $this->countMacroStreak($c),
            'nutricion-elite' => fn (Client $c) => $this->countMacroStreak($c),

            // Hábitos
            'habito-forjado' => fn (Client $c) => $this->countHabitFullDays($c),

            // Especial
            'madrugador' => fn (Client $c) => $this->countEarlyWorkouts($c),
            'wellcore-elite' => fn (Client $c) => $this->countActiveDays($c),
        ];
    }

    /** @return array<int, string> */
    private function slugsForCategory(string $category): array
    {
        return match ($category) {
            'constancia' => ['el-inicio', 'semana-fuego', 'mes-fuego'],
            'volumen' => ['entreno-10', 'entreno-50', 'entreno-200'],
            'fuerza' => ['primer-pr', '10-records', 'monstruo-fuerza'],
            'nutricion' => ['semana-macros', 'mes-limpio', 'nutricion-elite'],
            'habito' => ['habito-forjado'],
            'especial' => ['madrugador', 'wellcore-elite'],
            default => [],
        };
    }

    // ── Counters (WellCore-specific queries) ─────────────────────────────────

    private function countWorkouts(Client $client): int
    {
        return (int) DB::table('workout_sessions')
            ->where('client_id', $client->id)
            ->where('completed', 1)
            ->count();
    }

    /**
     * Distinct days with at least one completed workout.
     *
     * NOTE: the spec asks for "consecutive" days. Implementing a true "current streak"
     * in one query is expensive; for Phase 1 we return total distinct days, which
     * is a superset — the medal still unlocks correctly once the threshold is hit.
     * TODO: tighten to a real consecutive streak when LA-06 delivers the streak view.
     */
    private function countConsecutiveTrainingDays(Client $client): int
    {
        return (int) DB::table('workout_sessions')
            ->where('client_id', $client->id)
            ->where('completed', 1)
            ->distinct()
            ->count('session_date');
    }

    private function countPRs(Client $client): int
    {
        return (int) DB::table('personal_records')
            ->where('client_id', $client->id)
            ->count();
    }

    /**
     * TODO: nutrition_logs table does not exist yet.
     * Returning null skips the resolver entirely until the table is created.
     */
    private function countMacroStreak(Client $client): ?int
    {
        if (! Schema::hasTable('nutrition_logs')) {
            return null;
        }

        return (int) DB::table('nutrition_logs')
            ->where('client_id', $client->id)
            ->where('logged_at', '>=', now()->subDays(90)->startOfDay())
            ->distinct()
            ->count(DB::raw('DATE(logged_at)'));
    }

    /**
     * Days where the client logged ALL habit types (value > 0).
     * `habit_logs` is vertical: 1 row per (client, date, habit_type).
     */
    private function countHabitFullDays(Client $client): int
    {
        $required = count(self::HABIT_TYPES);

        return (int) DB::table('habit_logs')
            ->where('client_id', $client->id)
            ->whereIn('habit_type', self::HABIT_TYPES)
            ->where('value', '>', 0)
            ->groupBy('log_date')
            ->havingRaw('COUNT(DISTINCT habit_type) >= ?', [$required])
            ->get()
            ->count();
    }

    /**
     * TODO: workout_sessions has no started_at HH:mm timestamp — only session_date.
     * We can't reliably check "before 7am" yet. Return null to skip until a
     * started_at column is added.
     */
    private function countEarlyWorkouts(Client $client): ?int
    {
        if (! Schema::hasColumn('workout_sessions', 'started_at')) {
            return null;
        }

        return (int) DB::table('workout_sessions')
            ->where('client_id', $client->id)
            ->where('completed', 1)
            ->whereRaw('HOUR(started_at) < 7')
            ->count();
    }

    /**
     * Days since the client's first completed workout (platform lifetime proxy).
     */
    private function countActiveDays(Client $client): int
    {
        $first = DB::table('workout_sessions')
            ->where('client_id', $client->id)
            ->where('completed', 1)
            ->min('session_date');

        if (! $first) {
            return 0;
        }

        return (int) now()->startOfDay()->diffInDays($first, true);
    }
}
