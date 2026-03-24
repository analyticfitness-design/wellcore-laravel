<?php

namespace App\Livewire\Client;

use App\Models\AssignedPlan;
use App\Models\ClientXp;
use App\Models\WorkoutLog;
use App\Models\WorkoutPr;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Entrenamiento — WellCore'])]
class WorkoutPlayer extends Component
{
    /** All training days from the plan */
    public array $days = [];

    /** Index of the currently selected day */
    public int $currentDayIndex = 0;

    /** Exercises for the current day */
    public array $exercises = [];

    /** Active workout_sessions row ID */
    public ?int $sessionId = null;

    /** Whether the workout has been started */
    public bool $isActive = false;

    /** Show onboarding tutorial for first-time users */
    public bool $showTutorial = false;

    /** Timestamp when workout was started */
    public ?string $startTime = null;

    /** Per-exercise, per-set tracking data */
    public array $setData = [];

    /** The assigned plan ID for reference */
    public ?int $planId = null;

    /** Whether the client has an assigned training plan */
    public bool $hasPlan = false;

    /** Current day's display name */
    public string $dayName = '';

    /** Current day's muscle group */
    public string $muscleGroup = '';

    /** Elite plan: current week number (1-based) */
    public int $currentWeek = 1;

    /** Elite plan: total weeks available */
    public int $totalWeeks = 1;

    /** Whether the plan has weekly progressions (Elite plans) */
    public bool $hasProgressions = false;

    /**
     * All weeks' normalized days indexed by week number (1-based).
     * Populated in mount() for Elite plans so switchWeek() never re-fetches DB.
     * Protected so Livewire does NOT serialize this into the snapshot payload.
     */
    protected array $allWeeksDays = [];

    /** Block groups for superset/circuit display */
    public array $blockGroups = [];

    /**
     * Re-hydrate the protected $allWeeksDays from cache on every subsequent request.
     * This keeps Elite plan week-switching working while removing the array from the
     * Livewire snapshot (reducing payload by ~180 KB per request).
     */
    public function hydrate(): void
    {
        if (! $this->hasProgressions) {
            return;
        }

        $clientId = auth('wellcore')->id();
        if ($clientId) {
            $this->allWeeksDays = Cache::get("wp:weekdays:{$clientId}", []);
        }
    }

    public function mount(?int $day = null): void
    {
        $clientId = auth('wellcore')->id();

        // Show tutorial if client has never completed a workout
        $this->showTutorial = !WorkoutSession::where('client_id', $clientId)
            ->where('completed', true)
            ->exists();

        // Cache only a plain array — never cache Eloquent models (unserialize fails on file cache).
        $planData = Cache::remember("wp:plan:{$clientId}", 300, function () use ($clientId) {
            $row = AssignedPlan::select(['id', 'content', 'valid_from', 'client_id', 'created_at'])
                ->where('client_id', $clientId)
                ->where('plan_type', 'entrenamiento')
                ->where('active', true)
                ->latest('id')
                ->first();

            return $row ? $row->toArray() : null;
        });

        if (! $planData) {
            $this->hasPlan = false;
            return;
        }

        $this->hasPlan = true;
        $this->planId = $planData['id'];

        $content = is_array($planData['content'])
            ? $planData['content']
            : json_decode($planData['content'], true);

        // Normalize top-level key variants: 'days' | 'weeks' → 'dias'
        // Only accept array values — 'weeks' may be an integer (plan duration) in some plan formats
        if (! isset($content['dias']) || ! is_array($content['dias'])) {
            $fallback = $content['days'] ?? $content['weeks'] ?? null;
            if (is_array($fallback)) {
                $content['dias'] = $fallback;
            } elseif (! is_array($content['dias'] ?? null)) {
                unset($content['dias']); // Remove non-array dias so the empty check below handles it
            }
        }

        // Normalize exercises inside each flat day
        if (isset($content['dias']) && is_array($content['dias'])) {
            $content['dias'] = array_values(array_map(
                fn($d) => is_array($d) ? $this->normalizeDay($d) : $d,
                $content['dias']
            ));
        }

        // Format: { "plan": [{ "week": N, "days": [...] }] } → semanas
        // Some AI-generated plans use this week-keyed structure instead of 'semanas'.
        if (! isset($content['semanas']) && isset($content['plan']) && is_array($content['plan'])) {
            $first = reset($content['plan']);
            if (is_array($first) && (isset($first['days']) || isset($first['week']))) {
                $content['semanas'] = array_values(array_map(fn($w) => [
                    'semana' => $w['week'] ?? 1,
                    'dias'   => $w['days'] ?? [],
                ], $content['plan']));
                unset($content['plan']);
            }
        }

        // Elite plans may have weekly progressions: { "semanas": [{ "semana": 1, "dias": [...] }, ...] }
        if (isset($content['semanas']) && is_array($content['semanas'])) {
            $this->hasProgressions = true;
            $this->totalWeeks = count($content['semanas']);

            // Pre-normalize and store ALL weeks so switchWeek() never re-fetches DB.
            foreach ($content['semanas'] as $weekIndex => $weekData) {
                $weekNumber = $weekIndex + 1;
                $dias = $weekData['dias'] ?? $weekData['days'] ?? [];
                $this->allWeeksDays[$weekNumber] = array_values(array_map(
                    fn($d) => is_array($d) ? $this->normalizeDay($d) : $d,
                    $dias
                ));
            }

            // Determine current week based on plan start date
            $weeksActive = max(1, (int) ceil(Carbon::parse($planData['valid_from'] ?? $planData['created_at'])->diffInWeeks(now())) + 1);
            $this->currentWeek = min($weeksActive, $this->totalWeeks);

            $this->days = $this->allWeeksDays[$this->currentWeek] ?? [];

            // Cache the normalized weeks map so hydrate() can restore it without re-fetching the plan.
            Cache::put("wp:weekdays:{$clientId}", $this->allWeeksDays, 300);
        } else {
            $this->days = is_array($content['dias'] ?? null) ? $content['dias'] : [];
        }

        if (empty($this->days)) {
            $this->hasPlan = false;
            return;
        }

        // If $day parameter provided (1-based), use that index; otherwise default to first day
        if ($day !== null && $day >= 1 && $day <= count($this->days)) {
            $this->currentDayIndex = $day - 1;
        } else {
            $this->currentDayIndex = 0;
        }

        $this->loadDay();

        // Fix 4: Cache the active-session resume check for 60 seconds.
        // This avoids a DB hit on every Livewire re-mount (e.g. page refresh)
        // when no active session exists. The cache key is invalidated in startWorkout().
        $today = now()->toDateString();
        $planId = $this->planId;
        $dayName = $this->dayName;

        // Cache only the integer ID — never cache Eloquent models (unserialize fails on file cache).
        $existingSessionId = Cache::remember(
            "wp:session:{$clientId}:{$today}",
            60,
            function () use ($clientId, $planId, $dayName, $today) {
                return WorkoutSession::where('client_id', $clientId)
                    ->where('plan_id', $planId)
                    ->where('day_name', $dayName)
                    ->where('session_date', $today)
                    ->where('completed', false)
                    ->latest('id')
                    ->value('id'); // scalar, safe to cache
            }
        );

        if ($existingSessionId) {
            $existingSession = WorkoutSession::find($existingSessionId);
            if ($existingSession) {
                // Auto-abandon sessions older than 3 hours (stale / user couldn't complete)
                if ($existingSession->created_at->diffInHours(now()) >= 3) {
                    Cache::forget("wp:session:{$clientId}:{$today}");
                    // Don't resume — fall through to pre-workout state
                } else {
                    $this->sessionId = $existingSession->id;
                    $this->isActive = true;
                    $this->startTime = $existingSession->created_at->toIso8601String();
                    $this->rebuildSetDataFromLogs($existingSession);
                }
            }
        }
    }

    /**
     * Load exercises and metadata for the current day index.
     */
    protected function loadDay(): void
    {
        $day = $this->days[$this->currentDayIndex] ?? null;

        if (! $day) {
            return;
        }

        $this->dayName = $day['nombre'] ?? $day['name'] ?? $day['dia'] ?? 'Día ' . ($this->currentDayIndex + 1);
        $this->muscleGroup = $day['grupo_muscular'] ?? $day['muscle_group'] ?? $day['musculo'] ?? '';
        $this->exercises = $day['ejercicios'] ?? $day['exercises'] ?? $day['ejercicios_dia'] ?? [];

        $this->buildBlockGroups();

        if ($this->isActive) {
            $this->buildSetData();
        }
    }

    /**
     * Switch to a different week (Elite plans with progressions).
     */
    public function switchWeek(int $week): void
    {
        if ($this->isActive || !$this->hasProgressions) {
            return;
        }

        if ($week < 1 || $week > $this->totalWeeks) {
            return;
        }

        $this->currentWeek = $week;

        // Fix 1: Derive days from the in-memory allWeeksDays map populated in mount().
        // This eliminates the AssignedPlan::find() DB round-trip entirely.
        $this->days = $this->allWeeksDays[$week] ?? [];
        $this->currentDayIndex = 0;
        $this->loadDay();
        $this->setData = [];
    }

    /**
     * Build block groups for superset/circuit display.
     */
    protected function buildBlockGroups(): void
    {
        $this->blockGroups = [];
        $currentGroup = null;
        $groupIndex = 0;

        foreach ($this->exercises as $exIndex => $exercise) {
            $blockType = strtolower($exercise['bloque'] ?? $exercise['block_type'] ?? 'normal');

            if ($blockType === 'superset' || $blockType === 'circuito') {
                $groupId = $exercise['grupo_id'] ?? $exercise['group_id'] ?? $blockType . '_' . $groupIndex;

                if ($currentGroup && $currentGroup['id'] === $groupId) {
                    $currentGroup['exercises'][] = $exIndex;
                } else {
                    if ($currentGroup) {
                        $this->blockGroups[] = $currentGroup;
                    }
                    $currentGroup = [
                        'id' => $groupId,
                        'type' => $blockType,
                        'label' => $blockType === 'superset' ? 'SUPERSET' : 'CIRCUITO',
                        'rounds' => (int) ($exercise['rondas'] ?? $exercise['rounds'] ?? 1),
                        'exercises' => [$exIndex],
                    ];
                    $groupIndex++;
                }
            } else {
                if ($currentGroup) {
                    $this->blockGroups[] = $currentGroup;
                    $currentGroup = null;
                }
                $this->blockGroups[] = [
                    'id' => 'single_' . $exIndex,
                    'type' => 'normal',
                    'label' => null,
                    'exercises' => [$exIndex],
                ];
            }
        }

        if ($currentGroup) {
            $this->blockGroups[] = $currentGroup;
        }
    }

    /**
     * Switch to a different training day.
     */
    public function switchDay(int $index): void
    {
        if ($this->isActive) {
            return; // Cannot switch days mid-workout
        }

        if ($index < 0 || $index >= count($this->days)) {
            return;
        }

        $this->currentDayIndex = $index;
        $this->loadDay();
        $this->setData = [];
    }

    /**
     * Start the workout — create a session and initialize set tracking.
     */
    public function startWorkout(): void
    {
        if ($this->isActive) {
            return;
        }

        // Always re-derive exercises from the days array to guard against
        // Livewire snapshot deserialization issues with large/complex plans.
        $this->loadDay();

        if (empty($this->exercises)) {
            return;
        }

        $clientId = auth('wellcore')->id();

        $session = WorkoutSession::firstOrCreate(
            [
                'client_id'    => $clientId,
                'day_name'     => $this->dayName,
                'session_date' => now()->toDateString(),
            ],
            [
                'plan_id'   => $this->planId,
                'completed' => false,
            ]
        );

        // Fix 4: Bust the session resume cache so a subsequent mount() finds
        // the newly created session instead of returning null from cache.
        Cache::forget("wp:session:{$clientId}:" . now()->toDateString());

        $this->sessionId = $session->id;
        $this->isActive = true;
        $this->startTime = now()->toIso8601String();

        $this->buildSetData();
    }

    /**
     * Build the setData structure for each exercise's sets.
     * Pre-fills target weight from the client's last completed session.
     *
     * Uses a single batch query for all exercise names (eliminates N+1).
     */
    protected function buildSetData(): void
    {
        $clientId = auth('wellcore')->id();
        $this->setData = [];

        // Collect all distinct exercise names up front so we can batch-load
        // the previous weights in ONE query instead of one per exercise.
        $exerciseNames = collect($this->exercises)
            ->pluck('nombre')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // ONE query replaces N individual getLastWeight() calls.
        // Subquery joins workout_logs → workout_sessions to scope by client
        // and picks the highest-id (most recent) completed log per exercise.
        $lastWeights = [];
        if (! empty($exerciseNames)) {
            try {
                $lastWeights = WorkoutLog::select('workout_logs.exercise_name', 'workout_logs.weight_kg')
                    ->join(
                        DB::raw('(
                            SELECT wl2.exercise_name, MAX(wl2.id) as max_id
                            FROM workout_logs wl2
                            INNER JOIN workout_sessions ws2 ON ws2.id = wl2.session_id
                            WHERE ws2.client_id = ?
                              AND ws2.completed = 1
                              AND wl2.completed = 1
                              AND wl2.weight_kg IS NOT NULL
                            GROUP BY wl2.exercise_name
                        ) latest'),
                        function ($join) {
                            $join->on('workout_logs.exercise_name', '=', 'latest.exercise_name')
                                 ->on('workout_logs.id', '=', 'latest.max_id');
                        }
                    )
                    ->whereIn('workout_logs.exercise_name', $exerciseNames)
                    ->addBinding($clientId, 'join')
                    ->pluck('workout_logs.weight_kg', 'workout_logs.exercise_name')
                    ->map(fn ($w) => $w !== null ? (float) $w : null)
                    ->toArray();
            } catch (\Throwable $e) {
                // If the weight-lookup query fails, proceed without previous weights.
                // Exercises will still render; only the "Anterior" column will be empty.
                $lastWeights = [];
            }
        }

        foreach ($this->exercises as $exIndex => $exercise) {
            $seriesCount = (int) ($exercise['series'] ?? 4);
            $exerciseName = $exercise['nombre'] ?? '';
            // Resolved from the pre-loaded map — no query executed here.
            $lastWeight = $lastWeights[$exerciseName] ?? null;
            $targetReps = $exercise['repeticiones'] ?? '8-10';

            $sets = [];
            for ($s = 1; $s <= $seriesCount; $s++) {
                $sets[$s] = [
                    'set_number'    => $s,
                    'target_reps'   => $targetReps,
                    'target_weight' => $lastWeight,
                    'weight'        => $lastWeight,
                    'reps'          => '',
                    'completed'     => false,
                    'is_pr'         => false,
                ];
            }

            $this->setData[$exIndex] = $sets;
        }
    }

    /**
     * Rebuild setData from existing workout_logs when resuming a session.
     */
    protected function rebuildSetDataFromLogs(WorkoutSession $session): void
    {
        $logs = $session->logs()->get();

        // Build fresh setData first (already uses the batched weight query).
        $this->buildSetData();

        // Overlay completed logs on top.
        foreach ($logs as $log) {
            $exIndex = $log->block_order;

            if (isset($this->setData[$exIndex][$log->set_number])) {
                $this->setData[$exIndex][$log->set_number] = [
                    'set_number'    => $log->set_number,
                    'target_reps'   => $log->target_reps ?? $this->setData[$exIndex][$log->set_number]['target_reps'],
                    'target_weight' => $log->target_weight,
                    'weight'        => $log->weight_kg,
                    'reps'          => $log->reps,
                    'completed'     => (bool) $log->completed,
                    'is_pr'         => (bool) $log->is_pr,
                ];
            }
        }
    }

    /**
     * Complete a single set — save to workout_logs, check PR, dispatch rest timer.
     */
    public function completeSet(int $exerciseIndex, int $setNumber, mixed $weight, mixed $reps): void
    {
        if (! $this->isActive || ! $this->sessionId) {
            return;
        }

        $weight = is_numeric($weight) ? (float) $weight : 0;
        $reps = is_numeric($reps) ? (int) $reps : 0;

        if ($reps <= 0) {
            return;
        }

        $clientId = auth('wellcore')->id();
        $exercise = $this->exercises[$exerciseIndex] ?? null;

        if (! $exercise) {
            return;
        }

        $exerciseName = $exercise['nombre'] ?? 'Ejercicio';
        $targetReps = $this->setData[$exerciseIndex][$setNumber]['target_reps'] ?? null;
        $targetWeight = $this->setData[$exerciseIndex][$setNumber]['target_weight'] ?? null;

        // Check if this set was already logged (idempotency)
        $existing = WorkoutLog::where('session_id', $this->sessionId)
            ->where('exercise_name', $exerciseName)
            ->where('set_number', $setNumber)
            ->where('block_order', $exerciseIndex)
            ->first();

        if ($existing) {
            // Update existing log
            $existing->update([
                'weight_kg' => $weight,
                'reps' => $reps,
                'completed' => true,
            ]);
        } else {
            // Create new log
            WorkoutLog::create([
                'session_id' => $this->sessionId,
                'client_id' => $clientId,
                'exercise_name' => $exerciseName,
                'block_type' => 'normal',
                'block_order' => $exerciseIndex,
                'set_number' => $setNumber,
                'weight_kg' => $weight,
                'reps' => $reps,
                'target_reps' => $targetReps,
                'target_weight' => $targetWeight,
                'completed' => true,
                'is_pr' => false,
            ]);
        }

        // Check for PR
        $isPr = false;
        if ($weight > 0) {
            try {
                $pr = WorkoutPr::checkAndAward($clientId, $exerciseName, $weight, $reps);
                if ($pr) {
                    $isPr = true;
                    // Mark the log as PR
                    WorkoutLog::where('session_id', $this->sessionId)
                        ->where('exercise_name', $exerciseName)
                        ->where('set_number', $setNumber)
                        ->where('block_order', $exerciseIndex)
                        ->update(['is_pr' => true]);
                }
            } catch (\Throwable $e) {
                // PR check failure should never block set completion
                \Log::warning('WorkoutPr::checkAndAward failed', [
                    'client_id' => $clientId,
                    'exercise'  => $exerciseName,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        // Update local setData
        $this->setData[$exerciseIndex][$setNumber] = [
            'set_number' => $setNumber,
            'target_reps' => $targetReps,
            'target_weight' => $targetWeight,
            'weight' => $weight,
            'reps' => $reps,
            'completed' => true,
            'is_pr' => $isPr,
        ];

    }

    /**
     * Complete the entire workout — finalize session, award XP, update streak.
     */
    public function completeWorkout(?string $feeling = null, ?string $notes = null): void
    {
        if (! $this->isActive || ! $this->sessionId) {
            return;
        }

        $session = WorkoutSession::find($this->sessionId);

        if (! $session) {
            return;
        }

        // Calculate duration
        $startTime = Carbon::parse($this->startTime);
        $durationSec = (int) $startTime->diffInSeconds(now());

        $session->update([
            'completed'        => true,
            'duration_minutes' => (int) ($durationSec / 60),
            'feeling'          => $feeling,
            'notes'            => $notes,
        ]);

        // Calculate totals from logs
        $session->calculateTotals();

        // Award XP
        $xpEarned = $session->awardXp();

        // Update client XP + streak
        $this->updateClientXp($xpEarned);

        $this->isActive = false;

        // Redirect to workout summary
        $this->redirect(
            route('client.workout.summary', ['sessionId' => $session->id]),
            navigate: true
        );
    }

    /**
     * Update the ClientXp record — add XP, update streak.
     */
    protected function updateClientXp(int $xpEarned): void
    {
        $clientId = auth('wellcore')->id();

        $clientXp = ClientXp::firstOrCreate(
            ['client_id' => $clientId],
            [
                'xp_total' => 0,
                'level' => 1,
                'streak_days' => 0,
                'streak_last_date' => null,
                'streak_protected' => false,
            ]
        );

        $clientXp->xp_total += $xpEarned;

        // Level calculation: every 200 XP = 1 level
        $clientXp->level = max(1, (int) floor($clientXp->xp_total / 200) + 1);

        // Streak logic
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        if ($clientXp->streak_last_date === null) {
            $clientXp->streak_days = 1;
        } elseif ($clientXp->streak_last_date->toDateString() === $yesterday) {
            $clientXp->streak_days += 1;
        } elseif ($clientXp->streak_last_date->toDateString() === $today) {
            // Already logged today — no change
        } else {
            // Streak broken (unless protected)
            if ($clientXp->streak_protected) {
                $clientXp->streak_protected = false;
            } else {
                $clientXp->streak_days = 1;
            }
        }

        $clientXp->streak_last_date = $today;
        $clientXp->save();
    }

    /**
     * Parse a rest duration string into seconds.
     * Supports: "90s", "120s", "2min", "1:30", plain number.
     */
    protected function parseRestSeconds(string $rest): int
    {
        $rest = trim(strtolower($rest));

        // "90s" or "90 s"
        if (preg_match('/^(\d+)\s*s(eg)?$/i', $rest, $m)) {
            return (int) $m[1];
        }

        // "2min" or "2 min"
        if (preg_match('/^(\d+)\s*min$/i', $rest, $m)) {
            return (int) $m[1] * 60;
        }

        // "1:30" format
        if (preg_match('/^(\d+):(\d{2})$/', $rest, $m)) {
            return ((int) $m[1] * 60) + (int) $m[2];
        }

        // Plain number
        if (is_numeric($rest)) {
            return (int) $rest;
        }

        // Default fallback
        return 90;
    }

    /**
     * Computed helper: count how many sets are completed across all exercises.
     */
    public function getCompletedSetsCount(): int
    {
        $count = 0;
        foreach ($this->setData as $exerciseSets) {
            foreach ($exerciseSets as $set) {
                if ($set['completed'] ?? false) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Computed helper: total sets in workout.
     */
    public function getTotalSetsCount(): int
    {
        $count = 0;
        foreach ($this->setData as $exerciseSets) {
            $count += count($exerciseSets);
        }

        return $count;
    }

    /**
     * Computed helper: current volume (kg) from setData.
     */
    public function getCurrentVolume(): float
    {
        $volume = 0;
        foreach ($this->setData as $exerciseSets) {
            foreach ($exerciseSets as $set) {
                if ($set['completed'] ?? false) {
                    $volume += ((float) ($set['weight'] ?? 0)) * ((int) ($set['reps'] ?? 0));
                }
            }
        }

        return round($volume, 1);
    }

    /**
     * Abandon the current workout — preserves partial progress, marks session incomplete.
     * Does NOT delete logs so the client retains credit for completed sets.
     */
    public function abandonWorkout(): void
    {
        if (! $this->isActive || ! $this->sessionId) {
            return;
        }

        $session = WorkoutSession::find($this->sessionId);

        if (! $session) {
            $this->redirect(route('client.dashboard'));
            return;
        }

        // Mark as incomplete (partial) — never delete logs or the session row.
        // The client keeps credit for every set they already completed.
        $session->update(['completed' => false]);

        $this->sessionId = null;
        $this->isActive = false;
        $this->startTime = null;
        $this->setData = [];

        $this->redirect(route('client.dashboard'));
    }

    public function dismissTutorial(): void
    {
        $this->showTutorial = false;
    }

    public function render()
    {
        return view('livewire.client.workout-player', [
            'completedSets' => $this->getCompletedSetsCount(),
            'totalSets' => $this->getTotalSetsCount(),
            'currentVolume' => $this->getCurrentVolume(),
        ]);
    }

    /**
     * Normalize a single training day array to the keys the blade view expects.
     * Maps English keys → Spanish: exercises→ejercicios, name→nombre, sets→series, reps→repeticiones.
     */
    protected function normalizeDay(array $dia): array
    {
        if (! isset($dia['nombre']) && isset($dia['name'])) {
            $dia['nombre'] = $dia['name'];
        }

        if (! isset($dia['ejercicios'])) {
            $exFallback = $dia['exercises'] ?? $dia['sessions'] ?? null;
            if ($exFallback !== null) {
                $dia['ejercicios'] = $exFallback;
                unset($dia['exercises'], $dia['sessions']);
            }
        }

        if (isset($dia['ejercicios']) && is_array($dia['ejercicios'])) {
            foreach ($dia['ejercicios'] as &$ej) {
                if (! is_array($ej)) {
                    continue;
                }
                if (! isset($ej['nombre'])) {
                    $ej['nombre'] = $ej['name'] ?? $ej['exercise'] ?? $ej['ejercicio'] ?? '';
                }
                if (! isset($ej['series']) && isset($ej['sets'])) {
                    $ej['series'] = $ej['sets'];
                }
                if (! isset($ej['repeticiones']) && isset($ej['reps'])) {
                    $ej['repeticiones'] = $ej['reps'];
                }
                if (! isset($ej['descanso'])) {
                    $ej['descanso'] = $ej['rest'] ?? $ej['rest_seconds'] ?? '90s';
                }
            }
            unset($ej);
        }

        return $dia;
    }
}
