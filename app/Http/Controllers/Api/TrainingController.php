<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Services\ExerciseMediaService;
use App\Models\AssignedPlan;
use App\Models\BloodworkResult;
use App\Models\Checkin;
use App\Models\ClientXp;
use App\Models\HabitLog;
use App\Models\TrainingLog;
use App\Models\WorkoutLog;
use App\Models\WorkoutPr;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrainingController extends Controller
{
    use AuthenticatesVueRequests;

    // ─── Plan Viewer ───────────────────────────────────────────────────

    /**
     * GET /api/v/client/plan
     *
     * Full plan viewer: training, nutrition, supplements, habits, bloodwork.
     * Ports PlanViewer.php mount() logic.
     */
    public function plan(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $plans = AssignedPlan::where('client_id', $clientId)
            ->where('active', true)
            ->get();

        $trainingPlan = null;
        $nutritionPlan = null;
        $supplementPlan = null;
        $cicloPlan = null;

        foreach ($plans as $plan) {
            $content = is_array($plan->content)
                ? $plan->content
                : json_decode($plan->content, true);

            match ($plan->plan_type) {
                'entrenamiento' => $trainingPlan = $this->normalizeTrainingPlan($content),
                'nutricion' => $nutritionPlan = $content,
                'suplementacion' => $supplementPlan = $content,
                'ciclo_hormonal' => $cicloPlan = $content,
                default => null,
            };
        }

        // Week progression
        $currentWeek = 1;
        $totalWeeks = 1;
        $progressPct = 0;
        $planStartDate = null;

        if ($trainingPlan) {
            $totalWeeks = (int) ($trainingPlan['duracion_semanas'] ?? count($trainingPlan['semanas'] ?? []) ?: 1);
            $startDate = $trainingPlan['fecha_inicio'] ?? $client->fecha_inicio ?? null;

            if ($startDate) {
                $start = Carbon::parse($startDate);
                $planStartDate = $start->format('d M Y');
                $daysElapsed = max(0, $start->diffInDays(now()));
                $currentWeek = min($totalWeeks, (int) ceil(max(1, $daysElapsed) / 7));
                $totalDays = $totalWeeks * 7;
                $progressPct = $totalDays > 0 ? min(100, round(($daysElapsed / $totalDays) * 100, 1)) : 0;
            }
        }

        // Enrich training plan exercises with GIF/video URLs.
        // NOTE: must NOT use ?? [] in foreach when iterating by reference (&$semana, &$dia)
        // because ?? [] creates a temporary copy — references would point to the copy, not $trainingPlan.
        if ($trainingPlan) {
            $mediaService = app(ExerciseMediaService::class);

            if (!empty($trainingPlan['semanas'])) {
                foreach ($trainingPlan['semanas'] as $sIdx => &$semana) {
                    if (empty($semana['dias'])) {
                        continue;
                    }
                    foreach ($semana['dias'] as $dIdx => &$dia) {
                        $ejercicios = $dia['ejercicios'] ?? [];
                        if (empty($ejercicios)) {
                            continue;
                        }
                        try {
                            $mediaService->enrichWithMedia($ejercicios);
                        } catch (\Throwable $e) {
                            \Log::warning('GIF enrichment failed', ['sIdx' => $sIdx, 'dIdx' => $dIdx, 'error' => $e->getMessage()]);
                        }
                        $dia['ejercicios'] = $ejercicios;
                    }
                    unset($dia);
                }
                unset($semana);
            }

            if (!empty($trainingPlan['dias'])) {
                foreach ($trainingPlan['dias'] as $dIdx => &$dia) {
                    $ejercicios = $dia['ejercicios'] ?? [];
                    if (empty($ejercicios)) {
                        continue;
                    }
                    try {
                        $mediaService->enrichWithMedia($ejercicios);
                    } catch (\Throwable $e) {
                        \Log::warning('GIF enrichment failed (legacy dias)', ['dIdx' => $dIdx, 'error' => $e->getMessage()]);
                    }
                    $dia['ejercicios'] = $ejercicios;
                }
                unset($dia);
            }
        }

        // Habits (last 30 days)
        $habitData = $this->buildHabitData($clientId);

        // Bloodwork
        $bloodwork = BloodworkResult::where('client_id', $clientId)
            ->orderByDesc('test_date')
            ->get()
            ->toArray();

        $planType = strtolower($client->plan instanceof PlanType ? $client->plan->value : (string) ($client->plan ?? 'esencial'));

        return response()->json([
            'training_plan' => $trainingPlan,
            'nutrition_plan' => $nutritionPlan,
            'supplement_plan' => $supplementPlan,
            'ciclo_plan' => $cicloPlan,
            'plan_type' => $planType,
            'current_week' => $currentWeek,
            'total_weeks' => $totalWeeks,
            'progress_pct' => $progressPct,
            'plan_start_date' => $planStartDate,
            'habit_data' => $habitData['habits'],
            'habit_compliance' => $habitData['compliance'],
            'bloodwork' => $bloodwork,
        ]);
    }

    // ─── Training View (Weekly Calendar) ───────────────────────────────

    /**
     * GET /api/v/client/training
     *
     * Weekly training calendar with ISO week navigation.
     * Ports TrainingView.php render() logic.
     */
    public function training(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $year = (int) $request->query('year', now()->isoFormat('GGGG'));
        $week = (int) $request->query('week', now()->isoFormat('W'));

        $logs = TrainingLog::where('client_id', $clientId)
            ->where('year_num', $year)
            ->where('week_num', $week)
            ->get()
            ->keyBy(fn ($log) => $log->log_date->format('Y-m-d'));

        $startOfWeek = Carbon::now()->setISODate($year, $week, 1);
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dateKey = $day->format('Y-m-d');
            $days[] = [
                'date' => $dateKey,
                'dayNumber' => $day->format('d'),
                'dayName' => $day->locale('es')->isoFormat('ddd'),
                'isToday' => $day->isToday(),
                'completed' => isset($logs[$dateKey]) && $logs[$dateKey]->completed,
            ];
        }

        $completedCount = collect($days)->where('completed', true)->count();

        $isCurrentWeek = $year === (int) now()->isoFormat('GGGG')
            && $week === (int) now()->isoFormat('W');

        $monthCacheKey = "training:month_sessions:{$clientId}:".now()->format('Y-m');
        $monthSessions = Cache::remember($monthCacheKey, 300, function () use ($clientId) {
            return TrainingLog::where('client_id', $clientId)
                ->where('completed', true)
                ->whereBetween('log_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->count();
        });

        return response()->json([
            'year' => $year,
            'week' => $week,
            'days' => $days,
            'completed_count' => $completedCount,
            'month_sessions' => $monthSessions,
            'is_current_week' => $isCurrentWeek,
        ]);
    }

    /**
     * POST /api/v/client/training/toggle
     *
     * Toggle a training day's completion status.
     * Ports TrainingView.php toggleDay() logic.
     */
    public function toggleTrainingDay(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'date' => 'required|date|before_or_equal:today',
        ]);

        $date = $request->input('date');

        $log = TrainingLog::where('client_id', $clientId)
            ->where('log_date', $date)
            ->first();

        if ($log) {
            $log->update(['completed' => ! $log->completed]);
            $completed = $log->completed;
        } else {
            $parsed = Carbon::parse($date);
            TrainingLog::create([
                'client_id' => $clientId,
                'log_date' => $date,
                'completed' => true,
                'year_num' => (int) $parsed->isoFormat('GGGG'),
                'week_num' => (int) $parsed->isoFormat('W'),
            ]);
            $completed = true;
        }

        Cache::forget("training:month_sessions:{$clientId}:".now()->format('Y-m'));

        return response()->json([
            'date' => $date,
            'completed' => $completed,
        ]);
    }

    // ─── Workout Player ────────────────────────────────────────────────

    /**
     * GET /api/v/client/workout/{day?}
     *
     * Workout player data for a specific day. Ports WorkoutPlayer.php mount() logic
     * including plan normalization, week progression, block groups, and session auto-resume.
     */
    public function workout(Request $request, ?int $day = null): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        // Check if first-time user
        $showTutorial = ! WorkoutSession::where('client_id', $clientId)
            ->where('completed', true)
            ->exists();

        // Load plan
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
            return response()->json([
                'hasPlan' => false,
                'showTutorial' => $showTutorial,
            ]);
        }

        $planId = $planData['id'];
        $content = is_array($planData['content'])
            ? $planData['content']
            : json_decode($planData['content'], true);

        // Normalize top-level key variants
        if (! isset($content['dias']) || ! is_array($content['dias'])) {
            $fallback = $content['days'] ?? $content['weeks'] ?? null;
            if (is_array($fallback)) {
                $content['dias'] = $fallback;
            } elseif (! is_array($content['dias'] ?? null)) {
                unset($content['dias']);
            }
        }

        if (isset($content['dias']) && is_array($content['dias'])) {
            $content['dias'] = array_values(array_map(
                fn ($d) => is_array($d) ? $this->normalizeDay($d) : $d,
                $content['dias']
            ));
        }

        // Handle plan[] format
        if (! isset($content['semanas']) && isset($content['plan']) && is_array($content['plan'])) {
            $first = reset($content['plan']);
            if (is_array($first) && (isset($first['days']) || isset($first['week']))) {
                $content['semanas'] = array_values(array_map(fn ($w) => [
                    'semana' => $w['week'] ?? 1,
                    'dias' => $w['days'] ?? [],
                ], $content['plan']));
                unset($content['plan']);
            }
        }

        $hasProgressions = false;
        $currentWeek = 1;
        $totalWeeks = 1;
        $allWeeksDays = [];
        $days = [];

        // Elite plans with weekly progressions
        if (isset($content['semanas']) && is_array($content['semanas'])) {
            $hasProgressions = true;
            $totalWeeks = count($content['semanas']);

            foreach ($content['semanas'] as $weekIndex => $weekData) {
                $weekNumber = $weekIndex + 1;
                $dias = $weekData['dias'] ?? $weekData['days'] ?? [];
                $allWeeksDays[$weekNumber] = array_values(array_map(
                    fn ($d) => is_array($d) ? $this->normalizeDay($d) : $d,
                    $dias
                ));
            }

            $weeksActive = max(1, (int) ceil(Carbon::parse($planData['valid_from'] ?? $planData['created_at'])->diffInWeeks(now())) + 1);
            $currentWeek = min($weeksActive, $totalWeeks);

            // Allow client to request a specific week
            $requestedWeek = (int) $request->query('week', $currentWeek);
            if ($requestedWeek >= 1 && $requestedWeek <= $totalWeeks) {
                $currentWeek = $requestedWeek;
            }

            $days = $allWeeksDays[$currentWeek] ?? [];
        } else {
            $days = is_array($content['dias'] ?? null) ? $content['dias'] : [];
        }

        if (empty($days)) {
            return response()->json([
                'hasPlan' => false,
                'showTutorial' => $showTutorial,
            ]);
        }

        // Select day (1-based)
        $currentDayIndex = 0;
        if ($day !== null && $day >= 1 && $day <= count($days)) {
            $currentDayIndex = $day - 1;
        }

        $currentDay = $days[$currentDayIndex] ?? null;
        $dayName = $currentDay['nombre'] ?? $currentDay['name'] ?? $currentDay['dia'] ?? 'Dia '.($currentDayIndex + 1);
        $muscleGroup = $currentDay['grupo_muscular'] ?? $currentDay['muscle_group'] ?? $currentDay['musculo'] ?? '';
        $exercises = $currentDay['ejercicios'] ?? $currentDay['exercises'] ?? $currentDay['ejercicios_dia'] ?? [];

        // Build block groups
        $blockGroups = $this->buildBlockGroups($exercises);

        // Check for active session to auto-resume
        $activeSession = null;
        $setData = [];
        $today = now()->toDateString();

        $existingSession = WorkoutSession::where('client_id', $clientId)
            ->where('plan_id', $planId)
            ->where('day_name', $dayName)
            ->where('session_date', $today)
            ->where('completed', false)
            ->latest('id')
            ->first();

        if ($existingSession && $existingSession->created_at->diffInHours(now()) < 3) {
            $setData = $this->buildSetDataWithLogs($clientId, $exercises, $existingSession);
            $activeSession = [
                'id' => $existingSession->id,
                'startTime' => $existingSession->created_at->toIso8601String(),
                'setData' => $setData,
            ];
        }

        // Enrich exercises with last_weight / last_reps from previous sessions
        $exercises = $this->enrichExercisesWithHistory($clientId, $exercises);

        // Enrich with media (GIF + video) — silently skip if table unavailable
        try {
            app(ExerciseMediaService::class)->enrichWithMedia($exercises);
        } catch (\Throwable $e) {
            // ejercicios_fitcron may not exist in this environment
        }

        // Build full days array including exercises so Vue can switch days client-side
        // Use the enriched $exercises for the current day so video_url/gif_url are included
        $fullDays = array_map(fn ($d, $i) => [
            'index' => $i,
            'nombre' => $d['nombre'] ?? $d['name'] ?? $d['dia'] ?? 'Dia '.($i + 1),
            'grupo_muscular' => $d['grupo_muscular'] ?? $d['muscle_group'] ?? '',
            'ejercicios' => ($i === $currentDayIndex) ? $exercises : ($d['ejercicios'] ?? $d['exercises'] ?? $d['ejercicios_dia'] ?? []),
        ], $days, array_keys($days));

        return response()->json([
            // camelCase keys — matches Vue WorkoutPlayer expectations
            'hasPlan' => true,
            'showTutorial' => $showTutorial,
            'planId' => $planId,
            'hasProgressions' => $hasProgressions,
            'currentWeek' => $currentWeek,
            'totalWeeks' => $totalWeeks,
            'days' => $fullDays,
            'currentDayIndex' => $currentDayIndex,
            'dayName' => $dayName,
            'muscleGroup' => $muscleGroup,
            'exercises' => $exercises,
            'blockGroups' => $blockGroups,
            'activeSession' => $activeSession,
            'setData' => $setData,
        ]);
    }

    /**
     * POST /api/v/client/workout/start
     *
     * Start a workout session. Ports WorkoutPlayer.php startWorkout() logic.
     */
    public function startWorkout(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'day_index' => 'required|integer|min:0',
            'week'      => 'nullable|integer|min:1',
        ]);

        $dayIndex = (int) $request->input('day_index');
        $weekNum  = $request->input('week');

        // Load the active plan to derive plan_id and day_name
        $plan = AssignedPlan::where('client_id', $clientId)
            ->where('plan_type', 'entrenamiento')
            ->where('active', true)
            ->latest('id')
            ->first();

        $planId  = $plan?->id;
        $dayName = 'Día ' . ($dayIndex + 1);

        if ($plan) {
            $content = is_array($plan->content) ? $plan->content : json_decode($plan->content, true);

            if ($weekNum && isset($content['semanas'][$weekNum - 1])) {
                $dias    = $content['semanas'][$weekNum - 1]['dias'] ?? [];
                $dayName = $dias[$dayIndex]['nombre'] ?? $dias[$dayIndex]['dia'] ?? $dayName;
            } elseif (isset($content['dias'][$dayIndex])) {
                $day     = $content['dias'][$dayIndex];
                $dayName = $day['nombre'] ?? $day['dia'] ?? $day['name'] ?? $dayName;
            }
        }

        $session = WorkoutSession::firstOrCreate(
            [
                'client_id'    => $clientId,
                'day_name'     => $dayName,
                'session_date' => now()->toDateString(),
            ],
            [
                'plan_id'  => $planId,
                'completed' => false,
            ]
        );

        Cache::forget("wp:session:{$clientId}:".now()->toDateString());

        return response()->json([
            'session_id' => $session->id,
            'start_time' => $session->created_at->toIso8601String(),
        ]);
    }

    /**
     * POST /api/v/client/workout/complete-set
     *
     * Mark a set complete with weight/reps. Ports WorkoutPlayer.php completeSet() logic.
     */
    public function completeSet(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'session_id' => 'required|integer',
            'exercise_index' => 'required|integer|min:0',
            'set_number' => 'required|integer|min:1',
            'exercise_name' => 'required|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'reps' => 'required|integer|min:1',
            'target_reps' => 'nullable|string',
            'target_weight' => 'nullable|numeric',
            // Cardio fields
            'is_cardio' => 'nullable|boolean',
            'duration_minutes' => 'nullable|integer|min:0',
            'speed_kmh' => 'nullable|numeric|min:0',
            'incline_percent' => 'nullable|integer|min:0',
        ]);

        $sessionId = $request->input('session_id');
        $exerciseIndex = $request->input('exercise_index');
        $setNumber = $request->input('set_number');
        $exerciseName = $request->input('exercise_name');
        $weight = (float) ($request->input('weight', 0));
        $reps = (int) $request->input('reps');
        $isCardio = (bool) $request->input('is_cardio', false);

        // Verify session belongs to client
        $session = WorkoutSession::where('id', $sessionId)
            ->where('client_id', $clientId)
            ->where('completed', false)
            ->first();

        if (! $session) {
            return response()->json(['error' => 'Sesion no encontrada o ya completada.'], 404);
        }

        // Upsert the workout log
        $existing = WorkoutLog::where('session_id', $sessionId)
            ->where('exercise_name', $exerciseName)
            ->where('set_number', $setNumber)
            ->where('block_order', $exerciseIndex)
            ->first();

        $logData = $isCardio ? [
            'weight_kg' => 0,
            'reps' => $request->input('duration_minutes', 0),
            'is_cardio' => true,
            'duration_minutes' => $request->input('duration_minutes', 0),
            'speed_kmh' => $request->input('speed_kmh', 0),
            'incline_percent' => $request->input('incline_percent', 0),
            'completed' => true,
        ] : [
            'weight_kg' => $weight,
            'reps' => $reps,
            'completed' => true,
        ];

        if ($existing) {
            $existing->update($logData);
        } else {
            WorkoutLog::create(array_merge($logData, [
                'session_id' => $sessionId,
                'client_id' => $clientId,
                'exercise_name' => $exerciseName,
                'block_type' => 'normal',
                'block_order' => $exerciseIndex,
                'set_number' => $setNumber,
                'target_reps' => $request->input('target_reps'),
                'target_weight' => $request->input('target_weight'),
                'is_pr' => false,
            ]));
        }

        // Check for PR (non-cardio only)
        $isPr = false;
        if (! $isCardio && $weight > 0) {
            try {
                $pr = WorkoutPr::checkAndAward($clientId, $exerciseName, $weight, $reps);
                if ($pr) {
                    $isPr = true;
                    WorkoutLog::where('session_id', $sessionId)
                        ->where('exercise_name', $exerciseName)
                        ->where('set_number', $setNumber)
                        ->where('block_order', $exerciseIndex)
                        ->update(['is_pr' => true]);
                }
            } catch (\Throwable $e) {
                \Log::warning('WorkoutPr::checkAndAward failed', [
                    'client_id' => $clientId,
                    'exercise' => $exerciseName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'completed' => true,
            'is_pr' => $isPr,
        ]);
    }

    /**
     * POST /api/v/client/workout/finish
     *
     * Finish workout session. Ports WorkoutPlayer.php completeWorkout() logic.
     * Awards XP, detects PRs, updates streaks.
     */
    public function finishWorkout(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'session_id' => 'required|integer',
            'feeling' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string|max:1000',
        ]);

        $session = WorkoutSession::where('id', $request->input('session_id'))
            ->where('client_id', $clientId)
            ->where('completed', false)
            ->first();

        if (! $session) {
            return response()->json(['error' => 'Sesion no encontrada o ya completada.'], 404);
        }

        // Use frontend elapsed time if provided (more accurate), fallback to server diff
        $elapsedFromClient = (int) $request->input('elapsed', 0);
        $durationSec = $elapsedFromClient > 0
            ? $elapsedFromClient
            : (int) $session->created_at->diffInSeconds(now());

        // Cap at 4 hours max (14400 sec) to prevent absurd values from stale sessions
        $durationSec = min($durationSec, 14400);

        $session->update([
            'completed' => true,
            'duration_minutes' => max(1, (int) round($durationSec / 60)),
            'feeling' => $request->input('feeling'),
            'notes' => $request->input('notes'),
        ]);

        try {
            $session->calculateTotals();
        } catch (\Throwable $e) {
            \Log::warning('TrainingController: calculateTotals failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);
        }

        $xpEarned = 0;
        try {
            $xpEarned = $session->awardXp();
            $this->updateClientXp($clientId, $xpEarned);
        } catch (\Throwable $e) {
            \Log::warning('TrainingController: awardXp failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Count PRs from this session
        $prCount = WorkoutLog::where('session_id', $session->id)
            ->where('completed', true)
            ->where('is_pr', true)
            ->count();

        return response()->json([
            'session_id' => $session->id,
            'xp_earned' => $xpEarned,
            'pr_count' => $prCount,
            'duration' => $session->formattedDuration(),
        ]);
    }

    // ─── Workout Summary ───────────────────────────────────────────────

    /**
     * GET /api/v/client/workout-summary/{sessionId}
     *
     * Post-workout summary. Ports WorkoutSummary.php mount() logic.
     */
    public function workoutSummary(Request $request, string $sessionId): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        if ($sessionId === 'latest') {
            $session = WorkoutSession::with('logs')
                ->where('client_id', $clientId)
                ->where('completed', true)
                ->latest()
                ->firstOrFail();
        } else {
            $session = WorkoutSession::with('logs')
                ->where('client_id', $clientId)
                ->findOrFail((int) $sessionId);
        }

        $completedLogs = $session->logs->where('completed', true);
        $exerciseCount = $completedLogs->pluck('exercise_name')->unique()->count();
        $targetSets = $session->logs->count();

        $heaviestLog = $completedLogs->sortByDesc('weight_kg')->first();
        $maxWeight = $heaviestLog ? (float) $heaviestLog->weight_kg : 0;
        $maxWeightExercise = $heaviestLog ? $heaviestLog->exercise_name : null;

        $prCount = $completedLogs->where('is_pr', true)->count();

        $stats = [
            'duration' => $session->formattedDuration(),
            'duration_sec' => ($session->duration_minutes ?? 0) * 60,
            'max_weight' => $maxWeight,
            'max_weight_exercise' => $maxWeightExercise,
            'pr_count' => $prCount,
            'reps' => (int) $completedLogs->sum('reps'),
            'sets_completed' => $completedLogs->count(),
            'sets_total' => $targetSets,
            'exercises_count' => $exerciseCount,
            'total_volume' => (float) ($session->total_volume ?? 0),
        ];

        $cacheKey = "workout_summary_xp:{$session->id}";
        $xpEarned = Cache::remember($cacheKey, 86400 * 30, function () use ($session) {
            return $session->awardXp();
        });

        $prs = $completedLogs->where('is_pr', true)->map(function ($log) use ($clientId, $session) {
            // Look up the best weight for this exercise in any prior completed session
            $prevBest = \App\Models\WorkoutLog::where('client_id', $clientId)
                ->where('exercise_name', $log->exercise_name)
                ->where('completed', true)
                ->where('session_id', '!=', $session->id)
                ->whereHas('session', fn ($q) => $q->where('completed', true))
                ->orderByDesc('weight_kg')
                ->first();

            $previousWeight = $prevBest ? (float) $prevBest->weight_kg : null;
            $previousReps   = $prevBest ? (int)   $prevBest->reps       : null;

            return [
                'exercise'        => $log->exercise_name,
                'weight'          => (float) $log->weight_kg,
                'reps'            => (int) $log->reps,
                'previous_weight' => $previousWeight,
                'previous_reps'   => $previousReps,
            ];
        })->values()->toArray();

        $sessionHistory = WorkoutSession::where('client_id', $clientId)
            ->where('completed', true)
            ->where('id', '!=', $session->id)
            ->orderByDesc('session_date')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'date' => $s->session_date?->format('d M') ?? '-',
                'day_name' => $s->day_name ?? '-',
                'duration' => $s->formattedDuration(),
                'total_volume' => (float) ($s->total_volume ?? 0),
            ])
            ->toArray();

        return response()->json([
            'session' => [
                'id' => $session->id,
                'day_name' => $session->day_name,
                'session_date' => $session->session_date?->format('Y-m-d'),
                'feeling' => $session->feeling,
                'notes' => $session->notes,
            ],
            'stats' => $stats,
            'xp_earned' => $xpEarned,
            'prs' => $prs,
            'session_history' => $sessionHistory,
        ]);
    }

    /**
     * POST /api/v/client/workout-summary/{sessionId}/feeling
     *
     * Save workout feeling and notes. Ports WorkoutSummary.php saveFeedback().
     */
    public function saveWorkoutFeeling(Request $request, string $sessionId): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'feeling' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($sessionId === 'latest') {
            $session = WorkoutSession::where('client_id', $clientId)->where('completed', true)->latest()->firstOrFail();
        } else {
            $session = WorkoutSession::where('client_id', $clientId)->findOrFail((int) $sessionId);
        }

        $session->update([
            'feeling' => $request->input('feeling'),
            'notes' => $request->input('notes') ?: null,
        ]);

        return response()->json(['saved' => true]);
    }

    // ─── Check-in ──────────────────────────────────────────────────────

    /**
     * GET /api/v/client/checkin
     *
     * Get check-in form data and recent check-ins. Ports CheckinForm.php.
     */
    public function checkin(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $showTutorial = ! Checkin::where('client_id', $clientId)->exists();

        $dayOfWeek = now()->timezone('America/Bogota')->dayOfWeek;
        $isCheckinAvailable = in_array($dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY]);

        $cached = Cache::remember("checkin:recent:{$clientId}", 300, function () use ($clientId) {
            return Checkin::where('client_id', $clientId)
                ->orderByDesc('checkin_date')
                ->limit(10)
                ->get()
                ->toArray();
        });

        // Check if already submitted this week
        $weekLabel = now()->isoFormat('GGGG').'-W'.str_pad(now()->isoFormat('W'), 2, '0', STR_PAD_LEFT);
        $alreadySubmitted = Checkin::where('client_id', $clientId)
            ->where('week_label', $weekLabel)
            ->exists();

        return response()->json([
            'show_tutorial' => $showTutorial,
            'is_checkin_available' => $isCheckinAvailable,
            'already_submitted' => $alreadySubmitted,
            'week_label' => $weekLabel,
            'recent_checkins' => $cached,
        ]);
    }

    /**
     * POST /api/v/client/checkin
     *
     * Submit weekly check-in. Ports CheckinForm.php submit().
     */
    public function submitCheckin(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'bienestar' => 'required|integer|min:1|max:5',
            'dias_entrenados' => 'required|integer|min:0|max:7',
            'nutricion' => 'required|in:Si,No,Parcial',
            'rpe' => 'required|integer|min:1|max:10',
            'comentario' => 'nullable|string|max:1000',
        ]);

        $dayOfWeek = now()->timezone('America/Bogota')->dayOfWeek;
        if (! in_array($dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY])) {
            return response()->json([
                'error' => 'El check-in semanal solo esta disponible los viernes y sabados.',
            ], 422);
        }

        $weekLabel = now()->isoFormat('GGGG').'-W'.str_pad(now()->isoFormat('W'), 2, '0', STR_PAD_LEFT);

        $alreadySubmitted = Checkin::where('client_id', $clientId)
            ->where('week_label', $weekLabel)
            ->exists();

        if ($alreadySubmitted) {
            return response()->json([
                'error' => 'Ya enviaste tu check-in esta semana.',
            ], 422);
        }

        $checkin = Checkin::create([
            'client_id' => $clientId,
            'week_label' => $weekLabel,
            'checkin_date' => now()->toDateString(),
            'bienestar' => $request->input('bienestar'),
            'dias_entrenados' => $request->input('dias_entrenados'),
            'nutricion' => $request->input('nutricion'),
            'comentario' => $request->input('comentario'),
            'rpe' => $request->input('rpe'),
            'created_at' => now(),
        ]);

        Cache::forget("checkin:recent:{$clientId}");

        return response()->json([
            'saved' => true,
            'checkin_id' => $checkin->id,
        ]);
    }

    // ─── Private helpers ───────────────────────────────────────────────

    /**
     * Build habit data for the last 30 days. Ports PlanViewer.php loadHabits().
     */
    private function buildHabitData(int $clientId): array
    {
        $startDate = Carbon::now()->subDays(30);
        $today = Carbon::today();

        $logs = HabitLog::where('client_id', $clientId)
            ->where('log_date', '>=', $startDate)
            ->orderByDesc('log_date')
            ->get();

        $habitTypes = ['agua', 'sueno', 'entrenamiento', 'nutricion', 'suplementos'];
        $habitLabels = [
            'agua' => 'Agua',
            'sueno' => 'Sueno',
            'entrenamiento' => 'Entrenamiento',
            'nutricion' => 'Nutricion',
            'suplementos' => 'Suplementos',
        ];
        $habitIcons = [
            'agua' => 'droplet',
            'sueno' => 'moon',
            'entrenamiento' => 'dumbbell',
            'nutricion' => 'utensils',
            'suplementos' => 'pill',
        ];

        $habits = [];

        foreach ($habitTypes as $type) {
            $typeLogs = $logs->where('habit_type', $type);

            $avg = $typeLogs->count() > 0 ? round($typeLogs->avg('value'), 1) : 0;

            $streak = 0;
            $checkDate = $today->copy();
            for ($i = 0; $i < 30; $i++) {
                $dayLog = $typeLogs->first(function ($log) use ($checkDate) {
                    return $log->log_date->format('Y-m-d') === $checkDate->format('Y-m-d');
                });
                if ($dayLog && $dayLog->value > 0) {
                    $streak++;
                    $checkDate->subDay();
                } else {
                    break;
                }
            }

            $last7 = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $dayLog = $typeLogs->first(function ($log) use ($date) {
                    return $log->log_date->format('Y-m-d') === $date->format('Y-m-d');
                });
                $last7[] = [
                    'date' => $date->format('D'),
                    'value' => $dayLog ? $dayLog->value : 0,
                ];
            }

            $habits[] = [
                'type' => $type,
                'label' => $habitLabels[$type],
                'icon' => $habitIcons[$type],
                'streak' => $streak,
                'average' => $avg,
                'last7' => $last7,
            ];
        }

        $daysInMonth = $today->day;
        $monthStart = $today->copy()->startOfMonth();
        $daysWithLogs = $logs
            ->where('log_date', '>=', $monthStart)
            ->pluck('log_date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->unique()
            ->count();

        $compliance = $daysInMonth > 0 ? round(($daysWithLogs / $daysInMonth) * 100, 0) : 0;

        return [
            'habits' => $habits,
            'compliance' => $compliance,
        ];
    }

    /**
     * Normalize a training plan JSON structure. Ported from PlanViewer.php.
     */
    private function normalizeTrainingPlan(?array $content): ?array
    {
        if (! $content) {
            return null;
        }

        if (isset($content['semanas']) && is_array($content['semanas'])) {
            foreach ($content['semanas'] as &$semana) {
                $semana['dias'] = $this->normalizeDays($semana['dias'] ?? $semana['days'] ?? []);
                unset($semana['days']);
                $semana['numero'] = $semana['numero'] ?? $semana['number'] ?? $semana['semana'] ?? null;
                $semana['fase'] = $semana['fase'] ?? $semana['phase'] ?? $semana['nombre'] ?? null;
            }
            unset($semana);

            return $content;
        }

        if (! isset($content['dias']) && ! isset($content['days'])
            && isset($content['plan']) && is_array($content['plan'])) {
            $content['semanas'] = [];
            foreach ($content['plan'] as $idx => $week) {
                if (is_array($week) && (isset($week['days']) || isset($week['dias']))) {
                    $content['semanas'][] = [
                        'numero' => $week['week'] ?? $week['semana'] ?? ($idx + 1),
                        'fase' => $week['phase'] ?? $week['fase'] ?? $week['name'] ?? null,
                        'dias' => $this->normalizeDays($week['days'] ?? $week['dias'] ?? []),
                    ];
                }
            }
            if (! empty($content['semanas'])) {
                unset($content['plan']);

                return $content;
            }
        }

        // Handle weeks as array of week-objects: {weeks: [{week:1, days:[...]}, ...]}
        if (! isset($content['semanas']) && ! isset($content['dias']) && ! isset($content['days'])
            && isset($content['weeks']) && is_array($content['weeks'])) {
            $firstWeek = $content['weeks'][0] ?? null;
            if (is_array($firstWeek) && (isset($firstWeek['days']) || isset($firstWeek['dias']))) {
                $content['semanas'] = [];
                foreach ($content['weeks'] as $idx => $week) {
                    if (is_array($week)) {
                        $content['semanas'][] = [
                            'numero' => $week['week'] ?? $week['semana'] ?? ($idx + 1),
                            'fase'   => $week['phase'] ?? $week['fase'] ?? $week['name'] ?? null,
                            'dias'   => $this->normalizeDays($week['days'] ?? $week['dias'] ?? []),
                        ];
                    }
                }
                unset($content['weeks']);
                return $content;
            }
        }

        if (! isset($content['dias']) || ! is_array($content['dias'])) {
            $days = $content['days'] ?? null;
            $weeks = $content['weeks'] ?? null;
            if (is_array($days)) {
                $content['dias'] = $days;
            } elseif (is_array($weeks)) {
                $content['dias'] = $weeks;
            }
            unset($content['days']);
        }

        if (! isset($content['dias']) || ! is_array($content['dias'])) {
            return $content;
        }

        $content['dias'] = $this->normalizeDays($content['dias']);

        $duracion = (int) ($content['duracion_semanas'] ?? 1);
        if ($duracion > 1) {
            $content['semanas'] = [];
            for ($w = 1; $w <= $duracion; $w++) {
                $content['semanas'][] = [
                    'numero' => $w,
                    'fase' => $content['fases'][$w - 1] ?? null,
                    'dias' => $content['dias'],
                ];
            }
        } else {
            $content['semanas'] = [
                [
                    'numero' => 1,
                    'fase' => $content['fase'] ?? null,
                    'dias' => $content['dias'],
                ],
            ];
        }

        return $content;
    }

    /**
     * Normalize days array. Ported from PlanViewer.php.
     */
    private function normalizeDays(array $days): array
    {
        $normalized = [];
        foreach ($days as $dia) {
            if (! is_array($dia)) {
                continue;
            }

            if (! isset($dia['nombre']) && isset($dia['name'])) {
                $dia['nombre'] = $dia['name'];
            }
            if (! isset($dia['dia']) && isset($dia['day'])) {
                $dia['dia'] = $dia['day'];
            }

            if (! isset($dia['ejercicios'])) {
                $exercises = $dia['exercises'] ?? $dia['sessions'] ?? null;
                if ($exercises !== null) {
                    $dia['ejercicios'] = $exercises;
                    unset($dia['exercises'], $dia['sessions']);
                }
            }

            if (isset($dia['ejercicios']) && is_array($dia['ejercicios'])) {
                foreach ($dia['ejercicios'] as &$ej) {
                    if (! is_array($ej)) {
                        continue;
                    }
                    if (! isset($ej['nombre']) && isset($ej['name'])) {
                        $ej['nombre'] = $ej['name'];
                    }
                    if (! isset($ej['ejercicio']) && isset($ej['exercise'])) {
                        $ej['ejercicio'] = $ej['exercise'];
                    }
                    if (! isset($ej['series']) && isset($ej['sets'])) {
                        $ej['series'] = $ej['sets'];
                    }
                    if (! isset($ej['repeticiones']) && isset($ej['reps'])) {
                        $ej['repeticiones'] = $ej['reps'];
                    }
                }
                unset($ej);
            }

            $normalized[] = $dia;
        }

        return $normalized;
    }

    /**
     * Normalize a single training day (WorkoutPlayer format). Ported from WorkoutPlayer.php.
     */
    private function normalizeDay(array $dia): array
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

                $ej['is_cardio'] = $ej['is_cardio'] ?? $this->isCardioExercise($ej);
            }
            unset($ej);
        }

        return $dia;
    }

    /**
     * Detect cardio exercises by keywords. Ported from WorkoutPlayer.php.
     */
    private function isCardioExercise(array $exercise): bool
    {
        $name = mb_strtolower($exercise['nombre'] ?? $exercise['name'] ?? '');
        $type = mb_strtolower($exercise['tipo'] ?? $exercise['type'] ?? '');

        $cardioKeywords = [
            'caminadora', 'eliptica', 'eliptica', 'bicicleta', 'spinning', 'remo ergometro',
            'cardio', 'miss', 'hiit', 'trote', 'correr', 'treadmill', 'elliptical',
            'bike', 'rowing', 'caminata', 'estiramiento', 'descanso activo', 'recuperacion activa',
        ];

        foreach ($cardioKeywords as $keyword) {
            if (str_contains($name, $keyword) || str_contains($type, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build block groups for superset/circuit display. Ported from WorkoutPlayer.php.
     */
    private function buildBlockGroups(array $exercises): array
    {
        $blockGroups = [];
        $currentGroup = null;
        $groupIndex = 0;

        foreach ($exercises as $exIndex => $exercise) {
            $blockType = strtolower($exercise['bloque'] ?? $exercise['block_type'] ?? 'normal');

            if ($blockType === 'superset' || $blockType === 'circuito') {
                $groupId = $exercise['grupo_id'] ?? $exercise['group_id'] ?? $blockType.'_'.$groupIndex;

                if ($currentGroup && $currentGroup['id'] === $groupId) {
                    $currentGroup['exercises'][] = $exIndex;
                } else {
                    if ($currentGroup) {
                        $blockGroups[] = $currentGroup;
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
                    $blockGroups[] = $currentGroup;
                    $currentGroup = null;
                }
                $blockGroups[] = [
                    'id' => 'single_'.$exIndex,
                    'type' => 'normal',
                    'label' => null,
                    'exercises' => [$exIndex],
                ];
            }
        }

        if ($currentGroup) {
            $blockGroups[] = $currentGroup;
        }

        return $blockGroups;
    }

    /**
     * Build set data pre-filled with previous weights, then overlay existing logs.
     * Ported from WorkoutPlayer.php buildSetData() + rebuildSetDataFromLogs().
     */
    private function buildSetDataWithLogs(int $clientId, array $exercises, ?WorkoutSession $session = null): array
    {
        $exerciseNames = collect($exercises)
            ->pluck('nombre')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

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
            } catch (\Throwable) {
                $lastWeights = [];
            }
        }

        $setData = [];

        foreach ($exercises as $exIndex => $exercise) {
            $seriesCount = (int) ($exercise['series'] ?? 4);
            $exerciseName = $exercise['nombre'] ?? '';
            $lastWeight = $lastWeights[$exerciseName] ?? null;
            $targetReps = $exercise['repeticiones'] ?? '8-10';

            $sets = [];
            for ($s = 1; $s <= $seriesCount; $s++) {
                $sets[$s] = [
                    'set_number' => $s,
                    'target_reps' => $targetReps,
                    'target_weight' => $lastWeight,
                    'weight' => $lastWeight,
                    'reps' => '',
                    'completed' => false,
                    'is_pr' => false,
                ];
            }

            $setData[$exIndex] = $sets;
        }

        // Overlay existing logs if resuming a session
        if ($session) {
            $logs = $session->logs()->get();
            foreach ($logs as $log) {
                $exIndex = $log->block_order;
                if (isset($setData[$exIndex][$log->set_number])) {
                    $setData[$exIndex][$log->set_number] = [
                        'set_number' => $log->set_number,
                        'target_reps' => $log->target_reps ?? $setData[$exIndex][$log->set_number]['target_reps'],
                        'target_weight' => $log->target_weight,
                        'weight' => $log->weight_kg,
                        'reps' => $log->reps,
                        'completed' => (bool) $log->completed,
                        'is_pr' => (bool) $log->is_pr,
                    ];
                }
            }
        }

        return $setData;
    }

    /**
     * Enrich exercises with last_weight and last_reps from the client's
     * most recent completed session. Single query, no N+1.
     */
    private function enrichExercisesWithHistory(int $clientId, array $exercises): array
    {
        $names = collect($exercises)
            ->map(fn ($ex) => $ex['nombre'] ?? $ex['name'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($names)) {
            return $exercises;
        }

        try {
            $lastLogs = WorkoutLog::select('workout_logs.exercise_name', 'workout_logs.weight_kg', 'workout_logs.reps')
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
                ->whereIn('workout_logs.exercise_name', $names)
                ->addBinding($clientId, 'join')
                ->get()
                ->keyBy('exercise_name');
        } catch (\Throwable) {
            return $exercises;
        }

        foreach ($exercises as &$ex) {
            $exName = $ex['nombre'] ?? $ex['name'] ?? '';
            $lastLog = $lastLogs[$exName] ?? null;

            $ex['last_weight'] = $lastLog ? (float) $lastLog->weight_kg : null;
            $ex['last_reps'] = $lastLog ? (int) $lastLog->reps : null;
        }
        unset($ex);

        return $exercises;
    }

    /**
     * Update client XP and streak. Ported from WorkoutPlayer.php updateClientXp().
     */
    private function updateClientXp(int $clientId, int $xpEarned): void
    {
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
        $clientXp->level = max(1, (int) floor($clientXp->xp_total / 200) + 1);

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        if ($clientXp->streak_last_date === null) {
            $clientXp->streak_days = 1;
        } elseif ($clientXp->streak_last_date->toDateString() === $yesterday) {
            $clientXp->streak_days += 1;
        } elseif ($clientXp->streak_last_date->toDateString() === $today) {
            // Already logged today
        } else {
            if ($clientXp->streak_protected) {
                $clientXp->streak_protected = false;
            } else {
                $clientXp->streak_days = 1;
            }
        }

        $clientXp->streak_last_date = $today;
        $clientXp->save();
    }
}
