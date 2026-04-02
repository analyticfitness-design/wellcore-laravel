<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\AccountabilityPod;
use App\Models\Client;
use App\Models\PodMember;
use App\Models\PodMessage;
use App\Models\ProgressPhoto;
use App\Models\RiseHabitsLog;
use App\Models\RiseMeasurement;
use App\Models\RiseProgram;
use App\Models\RiseTracking;
use App\Models\WorkoutLog;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RiseController extends Controller
{
    use AuthenticatesVueRequests;

    // ─── Dashboard ──────────────────────────────────────────────────────

    /**
     * GET /api/v/rise/dashboard
     *
     * Rise dashboard data: greeting, program progress, weekly summary,
     * streak, quick stats, weekly tracking grid.
     * Ports Rise\Dashboard.php mount() logic.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        // Greeting
        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour < 12 => 'Buenos dias',
            $hour < 18 => 'Buenas tardes',
            default    => 'Buenas noches',
        };

        $clientName = explode(' ', $client->name ?? 'Usuario')[0];

        $cached = Cache::remember("rise:dashboard:{$clientId}", 300, function () use ($clientId) {
            return $this->buildRiseDashboardCache($clientId);
        });

        return response()->json([
            'greeting'                => $greeting,
            'clientName'              => $clientName,
            'hasProgram'              => $cached['hasProgram'],
            'startDate'               => $cached['startDate'],
            'endDate'                 => $cached['endDate'],
            'totalDays'               => $cached['totalDays'],
            'daysElapsed'             => $cached['daysElapsed'],
            'daysRemaining'           => $cached['daysRemaining'],
            'progressPct'             => $cached['progressPct'],
            'currentWeek'             => $cached['currentWeek'],
            'totalWeeks'              => $cached['totalWeeks'],
            'workoutsThisWeek'        => $cached['workoutsThisWeek'],
            'nutritionDaysThisWeek'   => $cached['nutritionDaysThisWeek'],
            'habitsCompletedThisWeek' => $cached['habitsCompletedThisWeek'],
            'currentStreak'           => $cached['currentStreak'],
            'latestWeight'            => $cached['latestWeight'],
            'weightChange'            => $cached['weightChange'],
            'totalTrackingDays'       => $cached['totalTrackingDays'],
            'overallAdherence'        => $cached['overallAdherence'],
            'weekDays'                => $cached['weekDays'],
        ]);
    }

    protected function buildRiseDashboardCache(int $clientId): array
    {
        $program = RiseProgram::where('client_id', $clientId)
            ->whereIn('status', ['active', 'activo'])
            ->first();

        $hasProgram    = false;
        $startDate     = null;
        $endDate       = null;
        $totalDays     = 84;
        $daysElapsed   = 0;
        $daysRemaining = 0;
        $progressPct   = 0;
        $currentWeek   = 1;
        $totalWeeks    = 4;

        if ($program) {
            $hasProgram = true;
            $startDate  = $program->start_date?->format('d M Y');
            $endDate    = $program->end_date?->format('d M Y');
            $totalDays  = $program->start_date && $program->end_date
                ? (int) Carbon::parse($program->start_date)->diffInDays($program->end_date)
                : 84;
            $daysElapsed   = $program->start_date ? (int) max(0, Carbon::parse($program->start_date)->diffInDays(now())) : 0;
            $daysRemaining = (int) max(0, $totalDays - $daysElapsed);
            $progressPct   = $totalDays > 0 ? min(100, round(($daysElapsed / $totalDays) * 100, 1)) : 0;

            $programJson = $program->personalized_program ?? [];
            $totalWeeks  = $programJson['plan_entrenamiento']['duracion_semanas']
                ?? count($programJson['plan_entrenamiento']['semanas'] ?? [])
                ?: 4;
            $currentWeek = min($totalWeeks, (int) ceil(max(1, $daysElapsed) / 7));
        }

        // Weekly summary
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = now()->endOfWeek(Carbon::SUNDAY);

        $weekTracking = RiseTracking::where('client_id', $clientId)
            ->whereBetween('log_date', [$startOfWeek, $endOfWeek])
            ->get();

        $workoutsThisWeek      = $weekTracking->where('training_done', true)->count();
        $nutritionDaysThisWeek = $weekTracking->where('nutrition_done', true)->count();

        $habitsCompletedThisWeek = 0;
        if ($program) {
            $habitsCompletedThisWeek = \App\Models\RiseDailyLog::where('rise_program_id', $program->id)
                ->whereBetween('log_date', [$startOfWeek, $endOfWeek])
                ->where('workout_completed', true)
                ->count();
        }

        // Streak
        $trackingDays = RiseTracking::where('client_id', $clientId)
            ->where('training_done', true)
            ->orderByDesc('log_date')
            ->pluck('log_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'));

        $streak    = 0;
        $checkDate = now()->format('Y-m-d');
        if (! $trackingDays->contains($checkDate)) {
            $checkDate = now()->subDay()->format('Y-m-d');
        }
        while ($trackingDays->contains($checkDate)) {
            $streak++;
            $checkDate = Carbon::parse($checkDate)->subDay()->format('Y-m-d');
        }

        // Quick stats
        $latest = RiseMeasurement::where('client_id', $clientId)->orderByDesc('log_date')->first();
        $first  = RiseMeasurement::where('client_id', $clientId)->orderBy('log_date')->first();

        $latestWeight = $latest ? (float) $latest->weight_kg : null;
        $weightChange = null;
        if ($latest && $first && $first->id !== $latest->id) {
            $weightChange = round((float) $latest->weight_kg - (float) $first->weight_kg, 1);
        }

        $totalTrackingDays = RiseTracking::where('client_id', $clientId)->count();
        $overallAdherence  = 0;
        if ($daysElapsed > 0) {
            $trainingDone     = RiseTracking::where('client_id', $clientId)->where('training_done', true)->count();
            $overallAdherence = round(($trainingDone / $daysElapsed) * 100, 0);
        }

        // Weekly grid
        $weekTrackingGrid = RiseTracking::where('client_id', $clientId)
            ->whereBetween('log_date', [$startOfWeek, $startOfWeek->copy()->addDays(6)])
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->log_date)->dayOfWeekIso);

        $dayLabels = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
        $today     = now()->dayOfWeekIso;
        $weekDays  = [];

        for ($i = 1; $i <= 7; $i++) {
            $entry = $weekTrackingGrid->get($i);
            $weekDays[] = [
                'label'         => $dayLabels[$i - 1],
                'trainingDone'  => $entry?->training_done ?? false,
                'nutritionDone' => $entry?->nutrition_done ?? false,
                'isToday'       => $i === $today,
            ];
        }

        return [
            'hasProgram'              => $hasProgram,
            'startDate'               => $startDate,
            'endDate'                 => $endDate,
            'totalDays'               => $totalDays,
            'daysElapsed'             => $daysElapsed,
            'daysRemaining'           => $daysRemaining,
            'progressPct'             => $progressPct,
            'currentWeek'             => $currentWeek,
            'totalWeeks'              => $totalWeeks,
            'workoutsThisWeek'        => $workoutsThisWeek,
            'nutritionDaysThisWeek'   => $nutritionDaysThisWeek,
            'habitsCompletedThisWeek' => $habitsCompletedThisWeek,
            'currentStreak'           => $streak,
            'latestWeight'            => $latestWeight,
            'weightChange'            => $weightChange,
            'totalTrackingDays'       => $totalTrackingDays,
            'overallAdherence'        => $overallAdherence,
            'weekDays'                => $weekDays,
        ];
    }

    // ─── Program View ───────────────────────────────────────────────────

    /**
     * GET /api/v/rise/program
     *
     * Program view with training, nutrition, habits tabs.
     * Ports Rise\ProgramView.php mount() logic.
     */
    public function program(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $riseProgram = RiseProgram::where('client_id', $clientId)
            ->whereIn('status', ['active', 'activo'])
            ->first();

        if (! $riseProgram) {
            return response()->json(['hasProgram' => false]);
        }

        $programJson = $riseProgram->personalized_program ?? [];

        $trainingPlan  = $this->normalizeTrainingPlan($programJson['plan_entrenamiento'] ?? null);
        $nutritionPlan = $programJson['plan_nutricion'] ?? null;
        $habitsPlan    = $programJson['plan_habitos']['habitos'] ?? $programJson['plan_habitos'] ?? [];

        $totalDays = $riseProgram->start_date && $riseProgram->end_date
            ? (int) Carbon::parse($riseProgram->start_date)->diffInDays($riseProgram->end_date)
            : 84;

        $daysElapsed = $riseProgram->start_date
            ? (int) max(0, Carbon::parse($riseProgram->start_date)->diffInDays(now()))
            : 0;

        $totalWeeks  = $trainingPlan['duracion_semanas']
            ?? count($trainingPlan['semanas'] ?? [])
            ?: 4;
        $currentWeek = min($totalWeeks, (int) ceil(max(1, $daysElapsed) / 7));
        $progressPct = $totalDays > 0 ? min(100, round(($daysElapsed / $totalDays) * 100, 1)) : 0;

        return response()->json([
            'hasProgram'       => true,
            'startDate'        => $riseProgram->start_date?->format('d M Y'),
            'endDate'          => $riseProgram->end_date?->format('d M Y'),
            'experienceLevel'  => $riseProgram->experience_level,
            'trainingLocation' => $riseProgram->training_location,
            'gender'           => $riseProgram->gender,
            'status'           => $riseProgram->status,
            'currentWeek'      => $currentWeek,
            'totalWeeks'       => $totalWeeks,
            'progressPct'      => $progressPct,
            'trainingPlan'     => $trainingPlan,
            'nutritionPlan'    => $nutritionPlan,
            'habitsPlan'       => $habitsPlan,
        ]);
    }

    // ─── Habits ─────────────────────────────────────────────────────────

    /**
     * GET /api/v/rise/habits
     *
     * Habits data with program-driven custom habits.
     * Ports Rise\Habits.php mount() logic.
     */
    public function habits(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $riseProgram = RiseProgram::where('client_id', $clientId)
            ->whereIn('status', ['active', 'activo'])
            ->latest('id')
            ->first();

        $riseProgramId = $riseProgram?->id;
        $programJson   = $riseProgram?->personalized_program ?? [];
        $habitsPlan    = $programJson['plan_habitos'] ?? [];

        // Today's log
        $today     = null;
        $water     = null;
        $sleep     = null;
        $steps     = null;
        $notes     = null;
        $todaySaved = false;
        $savedAt   = null;
        $habitsDone = [];

        if ($riseProgramId) {
            $today = RiseHabitsLog::where('rise_program_id', $riseProgramId)
                ->where('client_id', $clientId)
                ->where('log_date', now()->toDateString())
                ->first();

            if ($today) {
                $water     = $today->water_liters ? (float) $today->water_liters : null;
                $sleep     = $today->sleep_hours ? (float) $today->sleep_hours : null;
                $steps     = $today->steps;
                $notes     = $today->notes;
                $todaySaved = true;
                $savedAt   = $today->updated_at?->format('H:i');

                if ($today->habits_json !== null) {
                    $habitsDone = $today->habits_json;
                } else {
                    $habitsDone = [
                        '0' => (bool) $today->training_completed,
                        '1' => (bool) $today->nutrition_followed,
                        '2' => (bool) $today->meditation,
                    ];
                }
            }
        }

        // Weekly grid
        $weekDays = $this->buildHabitsWeeklyGrid($clientId, $riseProgramId, $habitsPlan);

        // Stats
        $stats = $this->buildHabitsStats($clientId, $riseProgramId);

        return response()->json([
            'riseProgramId' => $riseProgramId,
            'habitsPlan'    => $habitsPlan,
            'todaySaved'    => $todaySaved,
            'savedAt'       => $savedAt,
            'water'         => $water,
            'sleep'         => $sleep,
            'steps'         => $steps,
            'notes'         => $notes,
            'habitsDone'    => $habitsDone,
            'weekDays'      => $weekDays,
            'currentStreak' => $stats['currentStreak'],
            'completedDays' => $stats['completedDays'],
            'avgWater'      => $stats['avgWater'],
            'avgSleep'      => $stats['avgSleep'],
        ]);
    }

    /**
     * POST /api/v/rise/habits/toggle
     *
     * Save habits for today.
     * Ports Rise\Habits.php save() logic.
     */
    public function toggleHabit(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $validated = $request->validate([
            'water'      => 'nullable|numeric|min:0|max:10',
            'sleep'      => 'nullable|numeric|min:0|max:24',
            'steps'      => 'nullable|integer|min:0|max:100000',
            'notes'      => 'nullable|string|max:500',
            'habitsDone' => 'nullable|array',
        ]);

        $riseProgram = RiseProgram::where('client_id', $clientId)
            ->whereIn('status', ['active', 'activo'])
            ->latest('id')
            ->first();

        $riseProgramId = $riseProgram?->id;
        if (! $riseProgramId) {
            $riseProgramId = RiseProgram::where('client_id', $clientId)->latest('id')->value('id') ?? 0;
        }

        $habitsDone = $validated['habitsDone'] ?? [];

        RiseHabitsLog::updateOrCreate(
            [
                'rise_program_id' => $riseProgramId,
                'client_id'       => $clientId,
                'log_date'        => now()->toDateString(),
            ],
            [
                'water_liters'        => $validated['water'] ?? null,
                'sleep_hours'         => $validated['sleep'] ?? null,
                'steps'               => $validated['steps'] ?? null,
                'notes'               => $validated['notes'] ?? null,
                'habits_json'         => $habitsDone,
                'training_completed'  => (bool) ($habitsDone['0'] ?? false),
                'nutrition_followed'  => (bool) ($habitsDone['1'] ?? false),
                'meditation'          => (bool) ($habitsDone['2'] ?? false),
            ]
        );

        return response()->json(['saved' => true, 'savedAt' => now()->format('H:i')]);
    }

    protected function buildHabitsWeeklyGrid(int $clientId, ?int $riseProgramId, array $habitsPlan): array
    {
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);

        $logs = $riseProgramId
            ? RiseHabitsLog::where('rise_program_id', $riseProgramId)
                ->where('client_id', $clientId)
                ->whereBetween('log_date', [
                    $startOfWeek->toDateString(),
                    $startOfWeek->copy()->addDays(6)->toDateString(),
                ])
                ->get()
                ->keyBy(fn ($item) => Carbon::parse($item->log_date)->dayOfWeekIso)
            : collect();

        $dayLabels  = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
        $today      = now()->dayOfWeekIso;
        $totalHabits = count($habitsPlan) + 3;

        $weekDays = [];
        for ($i = 1; $i <= 7; $i++) {
            $entry      = $logs->get($i);
            $habitCount = 0;

            if ($entry) {
                $habitsJson = $entry->habits_json ?? [];
                foreach ($habitsJson as $done) {
                    if ($done) {
                        $habitCount++;
                    }
                }
                if ($entry->water_liters >= 2) $habitCount++;
                if ($entry->sleep_hours >= 7) $habitCount++;
                if ($entry->steps >= 5000) $habitCount++;
            }

            $weekDays[] = [
                'label'      => $dayLabels[$i - 1],
                'isToday'    => $i === $today,
                'hasEntry'   => $entry !== null,
                'habitCount' => $habitCount,
                'total'      => $totalHabits > 0 ? $totalHabits : 6,
                'habitsJson' => $entry?->habits_json ?? [],
                'water'      => $entry?->water_liters ? (float) $entry->water_liters : null,
                'sleep'      => $entry?->sleep_hours ? (float) $entry->sleep_hours : null,
                'steps'      => $entry?->steps,
            ];
        }

        return $weekDays;
    }

    protected function buildHabitsStats(int $clientId, ?int $riseProgramId): array
    {
        if (! $riseProgramId) {
            return ['currentStreak' => 0, 'completedDays' => 0, 'avgWater' => null, 'avgSleep' => null];
        }

        $allLogs = RiseHabitsLog::where('rise_program_id', $riseProgramId)
            ->where('client_id', $clientId)
            ->orderBy('log_date', 'desc')
            ->get();

        $completedDays = $allLogs->count();

        $currentStreak = 0;
        $checkDate     = now()->toDateString();
        foreach ($allLogs as $log) {
            if ($log->log_date->toDateString() === $checkDate) {
                $currentStreak++;
                $checkDate = Carbon::parse($checkDate)->subDay()->toDateString();
            } else {
                break;
            }
        }

        $withWater = $allLogs->whereNotNull('water_liters')->where('water_liters', '>', 0);
        $avgWater  = $withWater->count() > 0 ? round($withWater->avg('water_liters'), 1) : null;

        $withSleep = $allLogs->whereNotNull('sleep_hours')->where('sleep_hours', '>', 0);
        $avgSleep  = $withSleep->count() > 0 ? round($withSleep->avg('sleep_hours'), 1) : null;

        return compact('currentStreak', 'completedDays', 'avgWater', 'avgSleep');
    }

    // ─── Measurements ───────────────────────────────────────────────────

    /**
     * GET /api/v/rise/measurements
     *
     * Body measurements history with first/latest comparison.
     * Ports Rise\Measurements.php mount() + loadData() logic.
     */
    public function measurements(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $measurements = RiseMeasurement::where('client_id', $client->id)
            ->orderByDesc('log_date')
            ->get();

        $history = $measurements->map(fn ($m) => [
            'id'         => $m->id,
            'date'       => $m->log_date?->format('d M Y'),
            'weight_kg'  => $m->weight_kg ? (float) $m->weight_kg : null,
            'chest_cm'   => $m->chest_cm ? (float) $m->chest_cm : null,
            'waist_cm'   => $m->waist_cm ? (float) $m->waist_cm : null,
            'hips_cm'    => $m->hips_cm ? (float) $m->hips_cm : null,
            'thigh_cm'   => $m->thigh_cm ? (float) $m->thigh_cm : null,
            'arm_cm'     => $m->arm_cm ? (float) $m->arm_cm : null,
            'muscle_pct' => $m->muscle_pct ? (float) $m->muscle_pct : null,
            'fat_pct'    => $m->fat_pct ? (float) $m->fat_pct : null,
        ])->toArray();

        $latestMeasurement = null;
        $firstMeasurement  = null;

        if ($measurements->count() > 0) {
            $latest = $measurements->first();
            $latestMeasurement = $this->mapMeasurement($latest);

            if ($measurements->count() > 1) {
                $firstMeasurement = $this->mapMeasurement($measurements->last());
            }
        }

        return response()->json([
            'history'            => $history,
            'latestMeasurement'  => $latestMeasurement,
            'firstMeasurement'   => $firstMeasurement,
        ]);
    }

    /**
     * POST /api/v/rise/measurements
     *
     * Save a new body measurement.
     * Ports Rise\Measurements.php save() logic.
     */
    public function storeMeasurement(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'weight_kg'  => 'required|numeric|min:30|max:300',
            'chest_cm'   => 'nullable|numeric|min:30|max:200',
            'waist_cm'   => 'nullable|numeric|min:30|max:200',
            'hips_cm'    => 'nullable|numeric|min:30|max:200',
            'thigh_cm'   => 'nullable|numeric|min:20|max:100',
            'arm_cm'     => 'nullable|numeric|min:15|max:60',
            'muscle_pct' => 'nullable|numeric|min:0|max:100',
            'fat_pct'    => 'nullable|numeric|min:0|max:100',
        ]);

        $measurement = RiseMeasurement::create([
            'client_id'  => $client->id,
            'log_date'   => now()->toDateString(),
            ...$validated,
        ]);

        return response()->json([
            'saved'       => true,
            'measurement' => $this->mapMeasurement($measurement),
        ], 201);
    }

    private function mapMeasurement($m): array
    {
        return [
            'date'       => $m->log_date?->format('d M Y'),
            'weight_kg'  => $m->weight_kg ? (float) $m->weight_kg : null,
            'chest_cm'   => $m->chest_cm ? (float) $m->chest_cm : null,
            'waist_cm'   => $m->waist_cm ? (float) $m->waist_cm : null,
            'hips_cm'    => $m->hips_cm ? (float) $m->hips_cm : null,
            'thigh_cm'   => $m->thigh_cm ? (float) $m->thigh_cm : null,
            'arm_cm'     => $m->arm_cm ? (float) $m->arm_cm : null,
            'muscle_pct' => $m->muscle_pct ? (float) $m->muscle_pct : null,
            'fat_pct'    => $m->fat_pct ? (float) $m->fat_pct : null,
        ];
    }

    // ─── Photos ─────────────────────────────────────────────────────────

    /**
     * GET /api/v/rise/photos
     *
     * Progress photos grouped by date.
     * Ports Rise\Photos.php mount() + loadPhotos() logic.
     */
    public function photos(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $photos = ProgressPhoto::where('client_id', $client->id)
            ->orderBy('photo_date', 'desc')
            ->get();

        $grouped = $photos->groupBy(fn ($photo) => $photo->photo_date->format('Y-m-d'));

        $photosByDate = [];
        foreach ($grouped as $date => $datePhotos) {
            $photosByDate[] = [
                'date'       => $date,
                'formatted'  => Carbon::parse($date)->translatedFormat('d M Y'),
                'frente'     => $datePhotos->firstWhere('tipo', 'frente')?->filename,
                'frente_id'  => $datePhotos->firstWhere('tipo', 'frente')?->id,
                'perfil'     => $datePhotos->firstWhere('tipo', 'perfil')?->filename,
                'perfil_id'  => $datePhotos->firstWhere('tipo', 'perfil')?->id,
                'espalda'    => $datePhotos->firstWhere('tipo', 'espalda')?->filename,
                'espalda_id' => $datePhotos->firstWhere('tipo', 'espalda')?->id,
            ];
        }

        $firstDate  = $photos->count() > 0 ? $photos->last()->photo_date->format('Y-m-d') : null;
        $latestDate = $photos->count() > 0 ? $photos->first()->photo_date->format('Y-m-d') : null;

        return response()->json([
            'photosByDate' => $photosByDate,
            'firstDate'    => $firstDate,
            'latestDate'   => $latestDate,
        ]);
    }

    /**
     * POST /api/v/rise/photos
     *
     * Upload progress photo(s).
     * Ports Rise\Photos.php uploadPhotos() logic.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $request->validate([
            'upload_date'   => 'required|date',
            'photo_frente'  => 'nullable|image|max:5120',
            'photo_perfil'  => 'nullable|image|max:5120',
            'photo_espalda' => 'nullable|image|max:5120',
        ]);

        if (! $request->hasFile('photo_frente') && ! $request->hasFile('photo_perfil') && ! $request->hasFile('photo_espalda')) {
            return response()->json(['error' => 'Selecciona al menos una foto antes de guardar.'], 422);
        }

        $uploadDate = $request->input('upload_date');
        $uploadDir  = public_path('uploads/photos');

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $tiposToUpload = [
            'frente'  => $request->file('photo_frente'),
            'perfil'  => $request->file('photo_perfil'),
            'espalda' => $request->file('photo_espalda'),
        ];

        foreach ($tiposToUpload as $tipo => $photo) {
            if ($photo === null) {
                continue;
            }

            $extension = $photo->getClientOriginalExtension() ?: 'jpg';
            $filename  = "{$client->id}_{$uploadDate}_{$tipo}_" . time() . ".{$extension}";
            $destPath  = $uploadDir . DIRECTORY_SEPARATOR . $filename;

            copy($photo->getPathname(), $destPath);

            ProgressPhoto::where('client_id', $client->id)
                ->where('photo_date', $uploadDate)
                ->where('tipo', $tipo)
                ->delete();

            ProgressPhoto::create([
                'client_id'  => $client->id,
                'photo_date' => $uploadDate,
                'tipo'       => $tipo,
                'filename'   => $filename,
            ]);
        }

        return response()->json(['uploaded' => true], 201);
    }

    /**
     * DELETE /api/v/rise/photos/{id}
     *
     * Delete a progress photo.
     * Ports Rise\Photos.php deletePhoto() logic.
     */
    public function deletePhoto(Request $request, int $id): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $photo = ProgressPhoto::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (! $photo) {
            return response()->json(['error' => 'Foto no encontrada.'], 404);
        }

        $filePath = public_path('uploads/photos/' . $photo->filename);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $photo->delete();

        return response()->json(['deleted' => true]);
    }

    // ─── Chat ───────────────────────────────────────────────────────────

    /**
     * GET /api/v/rise/chat
     *
     * Pod messaging — get pod info and messages.
     * Ports Rise\Chat.php mount() + render() logic.
     */
    public function chat(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $membership = PodMember::where('client_id', $clientId)->first();

        if (! $membership) {
            return response()->json([
                'podId'       => null,
                'podName'     => null,
                'memberCount' => 0,
                'messages'    => [],
            ]);
        }

        $pod = AccountabilityPod::find($membership->pod_id);
        if (! $pod) {
            return response()->json([
                'podId'       => null,
                'podName'     => null,
                'memberCount' => 0,
                'messages'    => [],
            ]);
        }

        $memberCount = PodMember::where('pod_id', $pod->id)->count();

        $rawMessages = PodMessage::where('pod_id', $pod->id)
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();

        $clientIds = $rawMessages->pluck('client_id')->unique()->filter();
        $clients   = Client::whereIn('id', $clientIds)->get()->keyBy('id');

        $messages = [];
        foreach ($rawMessages as $msg) {
            $msgClient  = $clients->get($msg->client_id);
            $messages[] = [
                'id'      => $msg->id,
                'message' => $msg->message,
                'name'    => $msgClient->name ?? 'Usuario',
                'initial' => substr($msgClient->name ?? 'U', 0, 1),
                'isOwn'   => (string) $msg->client_id === (string) $clientId,
                'time'    => $msg->created_at?->format('H:i') ?? '',
                'date'    => $msg->created_at?->translatedFormat('d M') ?? '',
            ];
        }

        return response()->json([
            'podId'       => $pod->id,
            'podName'     => $pod->name,
            'memberCount' => $memberCount,
            'messages'    => $messages,
        ]);
    }

    /**
     * POST /api/v/rise/chat
     *
     * Send a pod message.
     * Ports Rise\Chat.php sendMessage() logic.
     */
    public function chatSend(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $validated = $request->validate([
            'message' => 'required|string|min:1|max:1000',
        ]);

        $membership = PodMember::where('client_id', $clientId)->first();
        if (! $membership) {
            return response()->json(['error' => 'No eres miembro de un pod.'], 403);
        }

        $pod = AccountabilityPod::find($membership->pod_id);
        if (! $pod) {
            return response()->json(['error' => 'Pod no encontrado.'], 404);
        }

        $msg = PodMessage::create([
            'pod_id'    => $pod->id,
            'client_id' => $clientId,
            'message'   => trim($validated['message']),
        ]);

        return response()->json([
            'sent'    => true,
            'message' => [
                'id'      => $msg->id,
                'message' => $msg->message,
                'name'    => $client->name ?? 'Usuario',
                'initial' => substr($client->name ?? 'U', 0, 1),
                'isOwn'   => true,
                'time'    => $msg->created_at?->format('H:i') ?? '',
                'date'    => $msg->created_at?->translatedFormat('d M') ?? '',
            ],
        ], 201);
    }

    // ─── Workout ────────────────────────────────────────────────────────

    /**
     * GET /api/v/rise/workout/{day?}
     *
     * Get workout data from Rise program content.
     * Ports Rise\WorkoutPlayer.php mount() data-source logic.
     */
    public function workout(Request $request, ?int $day = null): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $showTutorial = ! WorkoutSession::where('client_id', $clientId)
            ->where('completed', true)
            ->exists();

        $riseProgram = RiseProgram::where('client_id', $clientId)
            ->whereIn('status', ['active', 'activo'])
            ->first();

        if (! $riseProgram) {
            return response()->json(['hasPlan' => false, 'showTutorial' => $showTutorial]);
        }

        $programJson  = $riseProgram->personalized_program ?? [];
        $trainingPlan = $programJson['plan_entrenamiento'] ?? null;

        if (! $trainingPlan || empty($trainingPlan['semanas'])) {
            return response()->json(['hasPlan' => false, 'showTutorial' => $showTutorial]);
        }

        $totalWeeks = (int) ($trainingPlan['duracion_semanas'] ?? count($trainingPlan['semanas']));

        // Build all weeks data
        $allWeeksDays = [];
        foreach ($trainingPlan['semanas'] as $weekIndex => $weekData) {
            $weekNumber = $weekIndex + 1;
            $dias       = $weekData['dias'] ?? [];
            $allWeeksDays[$weekNumber] = array_values(
                array_map(fn ($d) => is_array($d) ? $this->normalizeDay($d) : $d, $dias)
            );
        }

        // Current week from program start date
        $startDate   = Carbon::parse($riseProgram->start_date ?? now());
        $weeksActive = max(1, (int) ceil($startDate->diffInWeeks(now())) + 1);
        $currentWeek = min($weeksActive, $totalWeeks);
        $days        = $allWeeksDays[$currentWeek] ?? [];

        if (empty($days)) {
            return response()->json(['hasPlan' => false, 'showTutorial' => $showTutorial]);
        }

        $currentDayIndex = 0;
        if ($day !== null && $day >= 1 && $day <= count($days)) {
            $currentDayIndex = $day - 1;
        }

        // Check for existing session today
        $todayStr  = now()->toDateString();
        $dayName   = $days[$currentDayIndex]['nombre'] ?? ('Dia ' . ($currentDayIndex + 1));
        $existing  = WorkoutSession::where('client_id', $clientId)
            ->where('day_name', $dayName)
            ->where('session_date', $todayStr)
            ->where('completed', false)
            ->latest('id')
            ->first();

        $activeSession = null;
        if ($existing && $existing->created_at->diffInHours(now()) < 3) {
            $activeSession = [
                'sessionId' => $existing->id,
                'startTime' => $existing->created_at->toIso8601String(),
            ];
        }

        return response()->json([
            'hasPlan'         => true,
            'showTutorial'    => $showTutorial,
            'totalWeeks'      => $totalWeeks,
            'currentWeek'     => $currentWeek,
            'allWeeksDays'    => $allWeeksDays,
            'days'            => $days,
            'currentDayIndex' => $currentDayIndex,
            'activeSession'   => $activeSession,
        ]);
    }

    /**
     * POST /api/v/rise/workout/start
     *
     * Start a Rise workout session.
     */
    public function startWorkout(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'day_name' => 'required|string|max:200',
            'week'     => 'nullable|integer|min:1',
        ]);

        $session = WorkoutSession::create([
            'client_id'    => $client->id,
            'day_name'     => $validated['day_name'],
            'session_date' => now()->toDateString(),
            'week_number'  => $validated['week'] ?? null,
            'completed'    => false,
            'source'       => 'rise',
        ]);

        return response()->json([
            'sessionId' => $session->id,
            'startTime' => $session->created_at->toIso8601String(),
        ], 201);
    }

    /**
     * POST /api/v/rise/workout/complete-set
     *
     * Record a completed set within a Rise workout session.
     */
    public function completeSet(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'session_id'    => 'required|integer',
            'exercise_name' => 'required|string|max:200',
            'set_number'    => 'required|integer|min:1',
            'reps'          => 'required|integer|min:0',
            'weight_kg'     => 'nullable|numeric|min:0',
            'unit'          => 'nullable|in:kg,lbs',
        ]);

        $session = WorkoutSession::where('id', $validated['session_id'])
            ->where('client_id', $client->id)
            ->where('completed', false)
            ->first();

        if (! $session) {
            return response()->json(['error' => 'Sesion no encontrada o ya completada.'], 404);
        }

        $weightKg = $validated['weight_kg'] ?? 0;
        if (($validated['unit'] ?? 'kg') === 'lbs' && $weightKg > 0) {
            $weightKg = round($weightKg * 0.453592, 2);
        }

        // Check for PR
        $isPr = false;
        if ($weightKg > 0) {
            $previousMax = WorkoutLog::where('client_id', $client->id)
                ->where('exercise_name', $validated['exercise_name'])
                ->where('completed', true)
                ->max('weight_kg');
            $isPr = $previousMax === null || $weightKg > (float) $previousMax;
        }

        $log = WorkoutLog::create([
            'session_id'    => $session->id,
            'client_id'     => $client->id,
            'exercise_name' => $validated['exercise_name'],
            'set_number'    => $validated['set_number'],
            'reps'          => $validated['reps'],
            'weight_kg'     => $weightKg,
            'completed'     => true,
            'is_pr'         => $isPr,
        ]);

        return response()->json([
            'logged' => true,
            'log_id' => $log->id,
            'is_pr'  => $isPr,
        ]);
    }

    /**
     * POST /api/v/rise/workout/finish
     *
     * Finish a Rise workout session.
     * Ports Rise\WorkoutPlayer.php completeWorkout() logic.
     */
    public function finishWorkout(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'session_id' => 'required|integer',
            'feeling'    => 'nullable|string|max:50',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $session = WorkoutSession::where('id', $validated['session_id'])
            ->where('client_id', $client->id)
            ->where('completed', false)
            ->first();

        if (! $session) {
            return response()->json(['error' => 'Sesion no encontrada o ya completada.'], 404);
        }

        $durationSec = (int) $session->created_at->diffInSeconds(now());

        $session->update([
            'completed'        => true,
            'duration_minutes' => (int) ($durationSec / 60),
            'feeling'          => $validated['feeling'] ?? null,
            'notes'            => $validated['notes'] ?? null,
        ]);

        try {
            $session->calculateTotals();
        } catch (\Throwable) {
        }

        $xpEarned = 0;
        try {
            $xpEarned = $session->awardXp();
        } catch (\Throwable) {
        }

        return response()->json([
            'completed' => true,
            'sessionId' => $session->id,
            'duration'  => (int) ($durationSec / 60),
            'xpEarned'  => $xpEarned,
        ]);
    }

    // ─── Workout Summary ────────────────────────────────────────────────

    /**
     * GET /api/v/rise/workout-summary/{sessionId}
     *
     * Workout summary with stats, PRs, XP.
     * Ports Rise\WorkoutSummary.php (extends Client\WorkoutSummary) mount() logic.
     */
    public function workoutSummary(Request $request, string $sessionId): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        if ($sessionId === 'latest') {
            $session = \App\Models\WorkoutSession::where('client_id', $clientId)
                ->where('completed', true)
                ->latest()
                ->firstOrFail();
            $sessionId = (string) $session->id;
        }

        $session = WorkoutSession::with('logs')
            ->where('client_id', $clientId)
            ->findOrFail($sessionId);

        $completedLogs = $session->logs->where('completed', true);
        $exerciseCount = $completedLogs->pluck('exercise_name')->unique()->count();
        $targetSets    = $session->logs->count();

        $heaviestLog       = $completedLogs->sortByDesc('weight_kg')->first();
        $maxWeight         = $heaviestLog ? (float) $heaviestLog->weight_kg : 0;
        $maxWeightExercise = $heaviestLog ? $heaviestLog->exercise_name : null;
        $prCount           = $completedLogs->where('is_pr', true)->count();

        $stats = [
            'duration'             => $session->formattedDuration(),
            'duration_sec'         => ($session->duration_minutes ?? 0) * 60,
            'max_weight'           => $maxWeight,
            'max_weight_exercise'  => $maxWeightExercise,
            'pr_count'             => $prCount,
            'reps'                 => (int) $completedLogs->sum('reps'),
            'sets_completed'       => $completedLogs->count(),
            'sets_total'           => $targetSets,
            'exercises_count'      => $exerciseCount,
        ];

        $xpCacheKey = "workout_summary_xp:{$session->id}";
        $xpEarned   = Cache::remember($xpCacheKey, 86400 * 30, fn () => $session->awardXp());

        $prs = $completedLogs->where('is_pr', true)->map(fn ($log) => [
            'exercise' => $log->exercise_name,
            'weight'   => (float) $log->weight_kg,
            'reps'     => $log->reps,
        ])->values()->toArray();

        $sessionHistory = WorkoutSession::where('client_id', $clientId)
            ->where('completed', true)
            ->where('id', '!=', $session->id)
            ->orderByDesc('session_date')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id'       => $s->id,
                'date'     => $s->session_date?->format('d M') ?? '-',
                'day_name' => $s->day_name ?? '-',
                'duration' => $s->formattedDuration(),
            ])
            ->toArray();

        return response()->json([
            'session'        => [
                'id'               => $session->id,
                'day_name'         => $session->day_name,
                'session_date'     => $session->session_date?->format('d M Y'),
                'feeling'          => $session->feeling,
                'notes'            => $session->notes,
            ],
            'stats'          => $stats,
            'xpEarned'       => $xpEarned,
            'prs'            => $prs,
            'sessionHistory' => $sessionHistory,
        ]);
    }

    // ─── Rise Profile ───────────────────────────────────────────────────

    /**
     * GET /api/v/rise/profile
     *
     * Rise profile — client info, program progress, stats.
     * Ports Rise\RiseProfile.php mount() logic.
     */
    public function profile(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $name    = $client->name ?? 'Usuario';
        $email   = $client->email ?? '';
        $initial = strtoupper(substr($name, 0, 1));

        $riseProgram = RiseProgram::where('client_id', $clientId)
            ->latest('id')
            ->first();

        $profile = [
            'name'             => $name,
            'email'            => $email,
            'initial'          => $initial,
            'startDate'        => null,
            'endDate'          => null,
            'experienceLevel'  => null,
            'trainingLocation' => null,
            'gender'           => null,
            'status'           => null,
            'progressPercent'  => 0,
            'daysInProgram'    => 0,
            'totalDays'        => 0,
            'measurementCount' => 0,
            'checkinsCount'    => 0,
            'habitsLogged'     => 0,
            'adherence'        => 0,
        ];

        if ($riseProgram) {
            $profile['startDate']        = $riseProgram->start_date?->translatedFormat('d M Y');
            $profile['endDate']          = $riseProgram->end_date?->translatedFormat('d M Y');
            $profile['experienceLevel']  = $riseProgram->experience_level;
            $profile['trainingLocation'] = $riseProgram->training_location;
            $profile['gender']           = $riseProgram->gender;
            $profile['status']           = $riseProgram->status;

            if ($riseProgram->start_date && $riseProgram->end_date) {
                $start = Carbon::parse($riseProgram->start_date);
                $end   = Carbon::parse($riseProgram->end_date);

                $profile['totalDays']      = (int) $start->diffInDays($end);
                $profile['daysInProgram']  = (int) min($start->diffInDays(now()), $profile['totalDays']);
                $profile['progressPercent'] = $profile['totalDays'] > 0
                    ? (int) min(round(($profile['daysInProgram'] / $profile['totalDays']) * 100), 100)
                    : 0;
            }

            $profile['measurementCount'] = RiseMeasurement::where('client_id', $clientId)->count();
            $profile['checkinsCount']    = RiseTracking::where('client_id', $clientId)->count();
            $profile['habitsLogged']     = RiseHabitsLog::where('rise_program_id', $riseProgram->id)
                ->where('client_id', $clientId)
                ->count();
            $profile['adherence'] = $profile['daysInProgram'] > 0
                ? round(($profile['checkinsCount'] / $profile['daysInProgram']) * 100, 1)
                : 0;
        }

        return response()->json($profile);
    }

    // ─── Helpers ────────────────────────────────────────────────────────

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

    /**
     * POST /api/v/rise/workout/uncomplete-set
     */
    public function uncompleteSet(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'session_id'     => 'required|integer',
            'exercise_name'  => 'required|string|max:200',
            'set_number'     => 'required|integer|min:1',
            'exercise_index' => 'nullable|integer',
        ]);

        $session = \App\Models\WorkoutSession::where('id', $validated['session_id'])
            ->where('client_id', $client->id)
            ->where('completed', false)
            ->first();

        if (! $session) {
            return response()->json(['error' => 'Sesion no encontrada.'], 404);
        }

        \App\Models\WorkoutLog::where('session_id', $validated['session_id'])
            ->where('exercise_name', $validated['exercise_name'])
            ->where('set_number', $validated['set_number'])
            ->delete();

        return response()->json(['uncompleted' => true]);
    }

    /**
     * POST /api/v/rise/workout/abandon
     */
    public function abandonWorkout(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'session_id' => 'required|integer',
        ]);

        $session = \App\Models\WorkoutSession::where('id', $validated['session_id'])
            ->where('client_id', $client->id)
            ->where('completed', false)
            ->first();

        if ($session) {
            $session->delete();
        }

        return response()->json(['abandoned' => true]);
    }

    /**
     * POST /api/v/rise/workout/dismiss-tutorial
     */
    public function dismissWorkoutTutorial(Request $request): JsonResponse
    {
        // Non-critical — just acknowledge
        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/v/rise/workout-summary/{sessionId}/feeling
     */
    public function saveWorkoutFeeling(Request $request, int $sessionId): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'feeling' => 'required|string|max:50',
        ]);

        \App\Models\WorkoutSession::where('id', $sessionId)
            ->where('client_id', $client->id)
            ->update(['feeling' => $validated['feeling']]);

        return response()->json(['saved' => true]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    /**
     * Normalizes training plan JSON to always expose a `semanas` array.
     *
     * Two legacy formats exist in the DB:
     *  A) { "semanas": [ { "semana":1, "dias":[...] } ] }  ← new format, pass-through
     *  B) { "dias": { "lunes": {...}, "martes": {...} } }  ← old flat format, wrap into 1 semana
     */
    private function normalizeTrainingPlan(?array $plan): ?array
    {
        if ($plan === null) {
            return null;
        }

        // Already has semanas array — pass-through
        if (!empty($plan['semanas']) && is_array($plan['semanas'])) {
            return $plan;
        }

        // Old format: dias is a keyed object {lunes:{...}, martes:{...}}
        if (!empty($plan['dias']) && is_array($plan['dias'])) {
            $diasArray = [];
            foreach ($plan['dias'] as $diaNombre => $diaData) {
                $diasArray[] = array_merge(
                    ['nombre' => ucfirst($diaNombre)],
                    is_array($diaData) ? $diaData : []
                );
            }

            $plan['semanas'] = [[
                'semana'      => 1,
                'fase'        => $plan['fase'] ?? 'Programa',
                'descripcion' => $plan['descripcion'] ?? '',
                'dias'        => $diasArray,
            ]];

            unset($plan['dias']);
        }

        return $plan;
    }
}
