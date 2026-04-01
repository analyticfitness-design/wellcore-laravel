<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AssignedPlan;
use App\Models\BiometricLog;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\ClientXp;
use App\Models\CoachMessage;
use App\Models\CoachRating;
use App\Models\Ticket;
use App\Models\HabitLog;
use App\Models\Metric;
use App\Models\Payment;
use App\Models\TrainingLog;
use App\Models\WeightLog;
use App\Models\WellcoreNotification;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    use AuthenticatesVueRequests;

    // ─── Dashboard ──────────────────────────────────────────────────────

    /**
     * GET /api/v/client/dashboard
     *
     * Returns all dashboard data: greeting, stats, plan progress,
     * streak calendar, weekly summary, daily missions, recent activity.
     * Uses Cache::remember with 5-min TTL (matching Livewire component).
     */
    public function dashboard(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        // Greeting based on time of day (never cached)
        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour < 12 => 'Buenos dias',
            $hour < 18 => 'Buenas tardes',
            default    => 'Buenas noches',
        };

        $clientName = explode(' ', $client->name ?? 'Usuario')[0];

        try {
            $planLabel = $client->plan?->label();
        } catch (\ValueError) {
            $planLabel = 'Plan';
        }

        // ── L2 cache: all DB-heavy data, 5-minute TTL ──
        $cached = Cache::remember("dashboard:{$clientId}", 300, function () use ($clientId) {
            return $this->buildDashboardCache($clientId);
        });

        // Plan info (has its own cache key)
        $planInfo = $this->loadPlanInfo($clientId);

        // Plan progress (no DB queries — reads created_at only)
        $planProgress = $this->loadPlanProgress($client);

        // Daily quote (deterministic per day-of-year)
        $dailyQuote = $this->getDailyQuote();

        // Daily missions (always fresh — never cached)
        $dailyMissions = $this->loadDailyMissions($clientId);

        return response()->json([
            // Greeting
            'greeting'          => $greeting,
            'clientName'        => $clientName,
            'planLabel'         => $planLabel,

            // Stats
            'streakDays'        => $cached['streakDays'],
            'checkinsThisMonth' => $cached['checkinsThisMonth'],
            'xpTotal'           => $cached['xpTotal'],
            'level'             => $cached['level'],
            'xpForNextLevel'    => $cached['xpForNextLevel'],
            'xpProgress'        => $cached['xpProgress'],
            'trainedThisWeek'   => $cached['trainedThisWeek'],

            // Weekly overview
            'weekDays'          => $cached['weekDays'],

            // Recent activity
            'recentActivity'    => $cached['recentActivity'],

            // Daily missions (fresh)
            'dailyMissions'     => $dailyMissions,

            // Check-in countdown
            'daysUntilCheckin'  => $cached['daysUntilCheckin'],
            'nextCheckinDate'   => $cached['nextCheckinDate'],

            // Weekly summary (last week)
            'lastWeekWorkouts'  => $cached['lastWeekWorkouts'],
            'lastWeekCheckins'  => $cached['lastWeekCheckins'],
            'lastWeekWeight'    => $cached['lastWeekWeight'],
            'hasLastWeekData'   => ($cached['lastWeekWorkouts'] > 0 || $cached['lastWeekCheckins'] > 0),

            // Coach info
            'coachName'         => $cached['coachName'],
            'coachInitials'     => $cached['coachInitials'],

            // Streak calendar (90 days)
            'streakCalendar'    => $cached['streakCalendar'],
            'calendarStreak'    => $cached['calendarStreak'],

            // Weight chart (90 days)
            'weightChartData'   => $cached['weightChartData'],

            // Plan info
            'hasActivePlan'     => $planInfo['hasActivePlan'],
            'planPhase'         => $planInfo['planPhase'],
            'planDaysActive'    => $planInfo['planDaysActive'],

            // Plan progress timeline
            'weeksActive'       => $planProgress['weeksActive'],
            'totalWeeks'        => $planProgress['totalWeeks'],
            'progressPercent'   => $planProgress['progressPercent'],
            'startDate'         => $planProgress['startDate'],

            // Daily quote
            'dailyQuote'        => $dailyQuote,

            // Onboarding
            'onboardingCompleted' => (bool) ($client->onboarding_completed ?? false),
            'planType'            => strtolower($client->plan?->value ?? 'esencial'),
        ]);
    }

    /**
     * Build the cached dashboard payload (mirrors Livewire Dashboard L2 cache).
     */
    private function buildDashboardCache(int $clientId): array
    {
        // --- Stats ---
        $xp            = ClientXp::where('client_id', $clientId)->first();
        $streakDays    = $xp?->streak_days ?? 0;
        $xpTotal       = $xp?->xp_total ?? 0;

        // XP formula: level = floor(xp / 200) + 1
        $currentLevel      = (int) floor($xpTotal / 200) + 1;
        $xpForCurrentLevel = ($currentLevel - 1) * 200;
        $xpForNext         = $currentLevel * 200;
        $xpProgress        = ($xpForNext > $xpForCurrentLevel)
            ? (int) round(($xpTotal - $xpForCurrentLevel) / ($xpForNext - $xpForCurrentLevel) * 100)
            : 100;

        $checkinsMonth = Checkin::where('client_id', $clientId)
            ->whereYear('checkin_date', now()->year)
            ->whereMonth('checkin_date', now()->month)
            ->count();

        $trainedWeek = TrainingLog::where('client_id', $clientId)
            ->where('year_num', now()->year)
            ->where('week_num', now()->isoWeek())
            ->where('completed', true)
            ->count();

        // --- Weekly overview ---
        $logs = TrainingLog::where('client_id', $clientId)
            ->where('year_num', now()->year)
            ->where('week_num', now()->isoWeek())
            ->where('completed', true)
            ->pluck('log_date')
            ->map(fn ($d) => Carbon::parse($d)->dayOfWeekIso)
            ->toArray();

        $dayLabels = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
        $today     = now()->dayOfWeekIso;
        $weekDays  = [];
        for ($i = 1; $i <= 7; $i++) {
            $weekDays[] = [
                'label'     => $dayLabels[$i - 1],
                'completed' => in_array($i, $logs),
                'isToday'   => $i === $today,
            ];
        }

        // --- Recent activity ---
        $activities = new Collection();

        $trainingLogs = TrainingLog::where('client_id', $clientId)
            ->where('completed', true)
            ->orderByDesc('log_date')
            ->limit(5)
            ->get();
        foreach ($trainingLogs as $log) {
            $activities->push([
                'type'        => 'training',
                'description' => 'Entrenamiento completado',
                'date'        => Carbon::parse($log->log_date),
            ]);
        }

        $checkins = Checkin::where('client_id', $clientId)
            ->orderByDesc('checkin_date')
            ->limit(5)
            ->get();
        foreach ($checkins as $checkin) {
            $activities->push([
                'type'        => 'checkin',
                'description' => 'Check-in semanal enviado',
                'date'        => Carbon::parse($checkin->checkin_date),
            ]);
        }

        $payments = Payment::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();
        foreach ($payments as $payment) {
            $activities->push([
                'type'        => 'payment',
                'description' => 'Pago registrado — ' . ($payment->plan?->label() ?? 'Plan'),
                'date'        => Carbon::parse($payment->created_at),
            ]);
        }

        $recentActivity = $activities
            ->sortByDesc('date')
            ->take(5)
            ->map(fn ($item) => [
                'type'        => $item['type'],
                'description' => $item['description'],
                'timeAgo'     => $item['date']->diffForHumans(),
            ])
            ->values()
            ->toArray();

        // --- Check-in countdown ---
        $lastCheckin = Checkin::where('client_id', $clientId)
            ->orderByDesc('checkin_date')
            ->first();

        if ($lastCheckin) {
            $nextDate       = Carbon::parse($lastCheckin->checkin_date)->addDays(7);
            $daysUntil      = (int) now()->startOfDay()->diffInDays($nextDate->copy()->startOfDay(), false);
            $nextCheckinStr = $nextDate->translatedFormat('l j M');
        } else {
            $daysUntil      = 0;
            $nextCheckinStr = now()->translatedFormat('l j M');
        }

        // --- Weekly summary (last week) ---
        $lastIsoWeek   = now()->subWeek()->isoWeek();
        $lastIsoYear   = now()->subWeek()->isoWeekYear;
        $lastWeekStart = now()->subWeek()->startOfWeek()->toDateString();
        $lastWeekEnd   = now()->subWeek()->endOfWeek()->toDateString();

        $lastWeekWorkouts = TrainingLog::where('client_id', $clientId)
            ->where('year_num', $lastIsoYear)
            ->where('week_num', $lastIsoWeek)
            ->where('completed', true)
            ->count();

        $lastWeekCheckins = Checkin::where('client_id', $clientId)
            ->whereBetween('checkin_date', [$lastWeekStart, $lastWeekEnd])
            ->count();

        $latestWeight = BiometricLog::where('client_id', $clientId)
            ->whereNotNull('weight_kg')
            ->where('weight_kg', '>', 0)
            ->orderByDesc('log_date')
            ->value('weight_kg');

        $lastWeekWeight = $latestWeight ? number_format((float) $latestWeight, 1) : null;

        // --- Coach info ---
        $coachName     = 'Tu Coach WellCore';
        $coachInitials = 'WC';
        $coachId       = AssignedPlan::where('client_id', $clientId)
            ->whereNotNull('assigned_by')
            ->orderByDesc('valid_from')
            ->value('assigned_by');

        if (! $coachId) {
            $coachId = CoachMessage::where('client_id', $clientId)
                ->whereNotNull('coach_id')
                ->orderByDesc('created_at')
                ->value('coach_id');
        }

        if ($coachId) {
            $coach = Admin::find($coachId);
            if ($coach && $coach->name) {
                $coachName = $coach->name;
                $parts     = explode(' ', trim($coach->name));
                $initials  = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
                $coachInitials = $initials ?: 'WC';
            }
        }

        // --- Streak calendar (90 days) ---
        $calendarLogs = TrainingLog::where('client_id', $clientId)
            ->where('completed', true)
            ->where('log_date', '>=', now()->subDays(90)->toDateString())
            ->selectRaw('DATE(log_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $streak    = 0;
        $checkDate = now()->copy();
        if (! isset($calendarLogs[$checkDate->format('Y-m-d')])) {
            $checkDate->subDay();
        }
        while (isset($calendarLogs[$checkDate->format('Y-m-d')])) {
            $streak++;
            $checkDate->subDay();
        }

        // --- Weight chart (90 days) ---
        $weightChartData = BiometricLog::where('client_id', $clientId)
            ->whereNotNull('weight_kg')
            ->where('weight_kg', '>', 0)
            ->where('log_date', '>=', now()->subDays(90)->toDateString())
            ->orderBy('log_date')
            ->get()
            ->map(fn ($log) => [
                'date'    => Carbon::parse($log->log_date)->format('d M'),
                'weight'  => round((float) $log->weight_kg, 1),
                'bodyFat' => $log->body_fat_pct ? round((float) $log->body_fat_pct, 1) : null,
            ])
            ->toArray();

        return [
            'streakDays'        => $streakDays,
            'xpTotal'           => $xpTotal,
            'level'             => $currentLevel,
            'xpForNextLevel'    => $xpForNext,
            'xpProgress'        => $xpProgress,
            'checkinsThisMonth' => $checkinsMonth,
            'trainedThisWeek'   => $trainedWeek,
            'weekDays'          => $weekDays,
            'recentActivity'    => $recentActivity,
            'daysUntilCheckin'  => $daysUntil,
            'nextCheckinDate'   => $nextCheckinStr,
            'lastWeekWorkouts'  => $lastWeekWorkouts,
            'lastWeekCheckins'  => $lastWeekCheckins,
            'lastWeekWeight'    => $lastWeekWeight,
            'coachName'         => $coachName,
            'coachInitials'     => $coachInitials,
            'streakCalendar'    => $calendarLogs,
            'calendarStreak'    => $streak,
            'weightChartData'   => $weightChartData,
        ];
    }

    /**
     * Plan info with its own cache key (mirrors Livewire loadPlanInfo).
     */
    private function loadPlanInfo(int $clientId): array
    {
        $planData = Cache::remember("client_plan_v3_{$clientId}", 300, function () use ($clientId) {
            $plan = AssignedPlan::where('client_id', $clientId)
                ->where('active', 1)
                ->orderByDesc('valid_from')
                ->select('plan_type', 'valid_from')
                ->first();

            if (! $plan) {
                return null;
            }

            return [
                'plan_type'  => (string) $plan->plan_type,
                'valid_from' => (string) $plan->getRawOriginal('valid_from'),
            ];
        });

        if ($planData && isset($planData['plan_type'])) {
            return [
                'hasActivePlan' => true,
                'planPhase'     => $planData['plan_type'],
                'planDaysActive' => (int) Carbon::parse($planData['valid_from'])->diffInDays(now()),
            ];
        }

        return [
            'hasActivePlan'  => false,
            'planPhase'      => null,
            'planDaysActive' => 0,
        ];
    }

    /**
     * Plan progress timeline (mirrors Livewire loadPlanProgress).
     */
    private function loadPlanProgress(Client $client): array
    {
        $createdAt = $client->created_at ?? $client->fecha_inicio ?? now();
        $startDate = Carbon::parse($createdAt)->format('d M Y');
        $weeksActive = (int) max(1, ceil(Carbon::parse($createdAt)->diffInWeeks(now())));
        $totalWeeks = 12;
        $progressPercent = (int) min(100, ($weeksActive / $totalWeeks) * 100);

        return [
            'startDate'       => $startDate,
            'weeksActive'     => $weeksActive,
            'totalWeeks'      => $totalWeeks,
            'progressPercent' => $progressPercent,
        ];
    }

    /**
     * Deterministic daily motivational quote (mirrors Livewire getDailyQuote).
     */
    private function getDailyQuote(): string
    {
        $quotes = [
            'La disciplina es el puente entre tus metas y tus logros.',
            'No entrenas para ser perfecto, entrenas para ser mejor.',
            'Tu cuerpo puede soportar casi todo. Es tu mente la que debes convencer.',
            'El dolor de hoy es la fuerza de manana.',
            'No se trata de ser el mejor. Se trata de ser mejor que ayer.',
            'La constancia supera al talento cuando el talento no es constante.',
            'Cada repeticion cuenta. Cada decision cuenta.',
            'El exito no se mide en kilos, se mide en habitos.',
            'Entrena porque amas tu cuerpo, no porque lo odies.',
            'El progreso no siempre es lineal, pero siempre vale la pena.',
            'Hoy es un buen dia para entrenar.',
            'Tu version futura te agradecera lo que hagas hoy.',
            'No hay atajos. Solo hay proceso.',
            'La ciencia no miente. La consistencia no falla.',
            'Menos excusas, mas evidencia.',
            'El metodo funciona cuando tu funcionas con el.',
            'Descansa, pero no renuncies.',
            'Cada check-in es un paso hacia tu mejor version.',
            'La transformacion empieza cuando dejas de buscar la perfeccion.',
            'Confiar en el proceso es parte del proceso.',
            'Tu coach esta aqui. Tu comunidad esta aqui. Solo falta tu decision.',
            'No necesitas motivacion. Necesitas un sistema.',
            'El gym no te cambia. Los habitos si.',
            'Lo que mides, mejora.',
            'Sin prisa, pero sin pausa.',
            'Hoy es dia de construir.',
            'Cada gota de sudor es una inversion.',
            'La ciencia respalda tu esfuerzo.',
            'No es sobre el peso en la barra. Es sobre el peso que cargas menos.',
            'Esto no es una dieta. Es tu nueva forma de vivir.',
        ];

        return $quotes[now()->dayOfYear % count($quotes)];
    }

    /**
     * Fresh daily missions (never cached — mirrors Livewire loadDailyMissions).
     */
    private function loadDailyMissions(int $clientId): array
    {
        $trainedToday = TrainingLog::where('client_id', $clientId)
            ->where('log_date', now()->toDateString())
            ->where('completed', true)
            ->exists();

        $checkinThisWeek = Checkin::where('client_id', $clientId)
            ->whereBetween('checkin_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ])
            ->exists();

        $weightThisWeek = WeightLog::where('client_id', $clientId)
            ->where('week_number', now()->isoWeek())
            ->where('year', now()->year)
            ->exists();

        $nutritionToday = HabitLog::where('client_id', $clientId)
            ->where('habit_type', 'nutricion')
            ->whereDate('log_date', today())
            ->where('value', '>=', 1)
            ->exists();

        return [
            [
                'key'       => 'training',
                'title'     => 'Completar entrenamiento',
                'completed' => $trainedToday,
                'icon'      => 'dumbbell',
            ],
            [
                'key'       => 'checkin',
                'title'     => 'Hacer check-in semanal',
                'completed' => $checkinThisWeek,
                'icon'      => 'checkin',
            ],
            [
                'key'       => 'weight',
                'title'     => 'Registrar peso',
                'completed' => $weightThisWeek,
                'icon'      => 'scale',
            ],
            [
                'key'       => 'nutrition',
                'title'     => 'Revisar plan de nutricion',
                'completed' => $nutritionToday,
                'icon'      => 'nutrition',
            ],
        ];
    }

    // ─── Metrics ────────────────────────────────────────────────────────

    /**
     * GET /api/v/client/metrics
     *
     * Metrics data: weight history, body composition, biometric logs,
     * training volume. Mirrors Livewire MetricsTracker render().
     */
    public function metrics(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        // Recent history — last 20 entries
        $history = Metric::where('client_id', $clientId)
            ->orderByDesc('log_date')
            ->limit(20)
            ->get();

        // Last 10 weight entries for mini-chart (oldest first)
        $chartData = Metric::where('client_id', $clientId)
            ->whereNotNull('peso')
            ->orderByDesc('log_date')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        // Weight delta (current vs. one month ago)
        $currentWeight  = $history->first()?->peso;
        $monthAgoWeight = Metric::where('client_id', $clientId)
            ->whereNotNull('peso')
            ->where('log_date', '<=', now()->subMonth()->toDateString())
            ->orderByDesc('log_date')
            ->value('peso');

        $weightChange = ($currentWeight && $monthAgoWeight)
            ? round((float) $currentWeight - (float) $monthAgoWeight, 2)
            : null;

        // Weight trend — last 90 days
        $weightTrend = Metric::where('client_id', $clientId)
            ->whereNotNull('peso')
            ->where('log_date', '>=', now()->subDays(90))
            ->orderBy('log_date')
            ->get(['log_date', 'peso'])
            ->map(fn ($m) => [
                'date'  => $m->log_date->format('d/m'),
                'value' => (float) $m->peso,
            ]);

        // Weekly check-ins — last 12 weeks
        $weeklyCheckins = Checkin::where('client_id', $clientId)
            ->where('checkin_date', '>=', now()->subWeeks(12))
            ->selectRaw('YEARWEEK(checkin_date, 1) as yw, COUNT(*) as cnt')
            ->groupBy('yw')
            ->orderBy('yw')
            ->get()
            ->map(fn ($r) => [
                'week' => (string) $r->yw,
                'cnt'  => (int) $r->cnt,
            ]);

        // Body composition — latest entry with both values
        $latestComposition = Metric::where('client_id', $clientId)
            ->whereNotNull('porcentaje_grasa')
            ->whereNotNull('porcentaje_musculo')
            ->orderByDesc('log_date')
            ->first(['porcentaje_grasa', 'porcentaje_musculo', 'log_date']);

        $composition = $latestComposition ? [
            'grasa'   => (float) $latestComposition->porcentaje_grasa,
            'musculo' => (float) $latestComposition->porcentaje_musculo,
            'otro'    => max(0, round(100 - (float) $latestComposition->porcentaje_grasa - (float) $latestComposition->porcentaje_musculo, 1)),
            'date'    => $latestComposition->log_date->format('d/m/Y'),
        ] : null;

        // Training volume — last 12 weeks
        $trainingVolume = WorkoutSession::where('client_id', $clientId)
            ->where('completed', true)
            ->where('session_date', '>=', now()->subWeeks(12))
            ->selectRaw('YEARWEEK(session_date, 1) as yw, COUNT(*) as sessions')
            ->groupBy('yw')
            ->orderBy('yw')
            ->get()
            ->map(fn ($r) => [
                'week'     => (string) $r->yw,
                'sessions' => (int) $r->sessions,
            ]);

        // First-time user tutorial flag
        $showTutorial = ! BiometricLog::where('client_id', $clientId)
            ->whereNotNull('weight_kg')
            ->exists();

        return response()->json([
            'history'           => $history,
            'chartData'         => $chartData,
            'currentWeight'     => $currentWeight,
            'weightChange'      => $weightChange,
            'weightTrend'       => $weightTrend,
            'weeklyCheckins'    => $weeklyCheckins,
            'latestComposition' => $composition,
            'trainingVolume'    => $trainingVolume,
            'showTutorial'      => $showTutorial,
        ]);
    }

    /**
     * POST /api/v/client/metrics
     *
     * Save a new metric entry (weight + body measurements).
     * Mirrors Livewire MetricsTracker saveMetric().
     */
    public function storeMetric(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $validated = $request->validate([
            'peso'               => 'required|numeric|min:20|max:300',
            'porcentaje_musculo' => 'nullable|numeric|min:0|max:100',
            'porcentaje_grasa'   => 'nullable|numeric|min:0|max:100',
            'notas'              => 'nullable|string|max:500',
            'chest'              => 'nullable|numeric|min:30|max:200',
            'waist'              => 'nullable|numeric|min:30|max:200',
            'hip'                => 'nullable|numeric|min:30|max:200',
            'thigh'              => 'nullable|numeric|min:20|max:100',
            'arm'                => 'nullable|numeric|min:15|max:60',
        ]);

        $logDate = now()->toDateString();

        Metric::updateOrCreate(
            ['client_id' => $clientId, 'log_date' => $logDate],
            [
                'peso'               => $validated['peso'],
                'porcentaje_musculo' => $validated['porcentaje_musculo'] ?? null,
                'porcentaje_grasa'   => $validated['porcentaje_grasa'] ?? null,
                'notas'              => $validated['notas'] ?? null,
            ]
        );

        // Sync to biometric_logs (weight + body measurements)
        $bioData = ['weight_kg' => (float) $validated['peso'] > 0 ? (float) $validated['peso'] : null];

        if (! empty($validated['porcentaje_grasa'])) {
            $bioData['body_fat_pct'] = (float) $validated['porcentaje_grasa'];
        }
        if (! empty($validated['porcentaje_musculo'])) {
            $bioData['muscle_pct'] = (float) $validated['porcentaje_musculo'];
        }
        if (! empty($validated['chest'])) {
            $bioData['chest_cm'] = (float) $validated['chest'];
        }
        if (! empty($validated['waist'])) {
            $bioData['waist_cm'] = (float) $validated['waist'];
        }
        if (! empty($validated['hip'])) {
            $bioData['hip_cm'] = (float) $validated['hip'];
        }
        if (! empty($validated['thigh'])) {
            $bioData['thigh_cm'] = (float) $validated['thigh'];
        }
        if (! empty($validated['arm'])) {
            $bioData['arm_cm'] = (float) $validated['arm'];
        }

        BiometricLog::updateOrCreate(
            ['client_id' => $clientId, 'log_date' => $logDate],
            $bioData
        );

        return response()->json([
            'message' => 'Metrica guardada exitosamente.',
            'peso'    => $validated['peso'],
        ]);
    }

    // ─── Profile ────────────────────────────────────────────────────────

    /**
     * GET /api/v/client/profile
     *
     * Profile data for editing. Mirrors Livewire ProfileEditor mount().
     */
    public function profile(Request $request): JsonResponse
    {
        $client  = $this->resolveClientOrFail($request);
        $profile = $client->profile;

        return response()->json([
            'name'             => $client->name ?? '',
            'email'            => $client->email ?? '',
            'city'             => $client->city ?? '',
            'bio'              => $client->bio ?? '',
            'birthDate'        => $client->birth_date?->format('Y-m-d') ?? '',
            'avatarUrl'        => $client->avatar_url ?? null,
            'peso'             => $profile?->peso ?? '',
            'altura'           => $profile?->altura ?? '',
            'objetivo'         => $profile?->objetivo ?? '',
            'whatsapp'         => $profile?->whatsapp ?? '',
            'nivel'            => $profile?->nivel ?? '',
            'lugarEntreno'     => $profile?->lugar_entreno ?? '',
            'diasDisponibles'  => $profile?->dias_disponibles ?? [],
            'restricciones'    => $profile?->restricciones ?? '',
        ]);
    }

    /**
     * PUT /api/v/client/profile
     *
     * Update profile fields. Mirrors Livewire ProfileEditor save().
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => ['required', 'email', 'max:255', Rule::unique('clients', 'email')->ignore($client->id)],
            'city'             => 'nullable|string|max:100',
            'bio'              => 'nullable|string|max:1000',
            'birthDate'        => 'nullable|date',
            'peso'             => 'nullable|numeric|min:30|max:300',
            'altura'           => 'nullable|numeric|min:100|max:250',
            'objetivo'         => 'nullable|string|max:500',
            'whatsapp'         => 'nullable|string|max:20',
            'nivel'            => 'nullable|in:principiante,intermedio,avanzado',
            'lugarEntreno'     => 'nullable|in:gym,casa,ambos',
            'diasDisponibles'  => 'nullable|array',
            'restricciones'    => 'nullable|string|max:1000',
        ]);

        $client->update([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'city'       => $validated['city'] ?? null,
            'bio'        => $validated['bio'] ?? null,
            'birth_date' => $validated['birthDate'] ?? null,
        ]);

        ClientProfile::updateOrCreate(
            ['client_id' => $client->id],
            [
                'peso'              => $validated['peso'] ?? null,
                'altura'            => $validated['altura'] ?? null,
                'objetivo'          => $validated['objetivo'] ?? null,
                'whatsapp'          => $validated['whatsapp'] ?? null,
                'nivel'             => $validated['nivel'] ?? null,
                'lugar_entreno'     => $validated['lugarEntreno'] ?? null,
                'dias_disponibles'  => $validated['diasDisponibles'] ?? [],
                'restricciones'     => $validated['restricciones'] ?? null,
            ]
        );

        return response()->json(['message' => 'Perfil actualizado exitosamente.']);
    }

    // ─── Settings ───────────────────────────────────────────────────────

    /**
     * GET /api/v/client/settings
     *
     * Settings data (profile tab). Mirrors Livewire ClientSettings mount().
     */
    public function settings(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        return response()->json([
            'name'  => $client->name ?? '',
            'email' => $client->email ?? '',
            'phone' => $client->phone ?? '',
        ]);
    }

    /**
     * PUT /api/v/client/settings
     *
     * Update settings profile fields.
     * Mirrors Livewire ClientSettings updateProfile().
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('clients', 'email')->ignore($client->id)],
            'phone' => 'nullable|string|max:30',
        ]);

        $data = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        // Only update phone if the column is fillable
        if (in_array('phone', $client->getFillable(), true)) {
            $data['phone'] = $validated['phone'] ?? null;
        }

        $client->update($data);

        return response()->json(['message' => 'Configuracion actualizada.']);
    }

    /**
     * PUT /api/v/client/settings/password
     *
     * Change password. Mirrors Livewire ClientSettings changePassword().
     */
    public function changePassword(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'currentPassword' => 'required|string',
            'newPassword'     => 'required|string|min:8',
            'confirmPassword' => 'required|string',
        ]);

        if (! password_verify($validated['currentPassword'], $client->password_hash)) {
            return response()->json([
                'message' => 'La contrasena actual es incorrecta.',
            ], 422);
        }

        if ($validated['newPassword'] !== $validated['confirmPassword']) {
            return response()->json([
                'message' => 'Las contrasenas no coinciden.',
            ], 422);
        }

        $client->update([
            'password_hash' => bcrypt($validated['newPassword']),
        ]);

        return response()->json(['message' => 'Contrasena actualizada exitosamente.']);
    }

    // ─── Notifications ──────────────────────────────────────────────────

    /**
     * GET /api/v/client/notifications
     *
     * Unread notifications list. Mirrors Livewire NotificationBell.
     */
    public function notifications(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $rows = WellcoreNotification::where('user_id', $clientId)
            ->where('user_type', 'client')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'title', 'body', 'link', 'read_at', 'created_at']);

        $notifications = $rows->map(fn ($n) => [
            'id'         => $n->id,
            'title'      => $n->title,
            'body'       => $n->body,
            'link'       => $n->link,
            'read_at'    => $n->read_at?->toIso8601String(),
            'created_at' => $n->created_at?->diffForHumans(),
        ])->toArray();

        $unreadCount = $rows->whereNull('read_at')->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }

    /**
     * POST /api/v/client/notifications/{id}/read
     *
     * Mark a single notification as read.
     * Mirrors Livewire NotificationBell markAsRead().
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $updated = WellcoreNotification::where('id', $id)
            ->where('user_id', $client->id)
            ->where('user_type', 'client')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (! $updated) {
            return response()->json(['message' => 'Notificacion no encontrada o ya leida.'], 404);
        }

        return response()->json(['message' => 'Notificacion marcada como leida.']);
    }

    /**
     * POST /api/v/client/notifications/read-all
     *
     * Mark all unread notifications as read.
     * Mirrors Livewire NotificationBell markAllAsRead().
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        WellcoreNotification::where('user_id', $client->id)
            ->where('user_type', 'client')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Todas las notificaciones marcadas como leidas.']);
    }

    // ─── Coach Feedback ─────────────────────────────────────────────────

    /**
     * GET /api/v/client/coach-feedback
     */
    public function coachFeedback(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $coachId = DB::table('coach_messages')
            ->where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->value('coach_id');

        if (! $coachId) {
            return response()->json(['coach' => null, 'ratings' => []]);
        }

        $coach = DB::table('coach_profiles')
            ->join('admins', 'admins.id', '=', 'coach_profiles.admin_id')
            ->where('coach_profiles.admin_id', $coachId)
            ->select('admins.name', 'coach_profiles.bio', 'coach_profiles.photo_url', 'coach_profiles.city')
            ->first();

        $ratings = CoachRating::where('client_id', $client->id)
            ->where('coach_id', $coachId)
            ->orderByDesc('created_at')
            ->get(['id', 'rating', 'comment', 'created_at'])
            ->map(fn ($r) => [
                'id'         => $r->id,
                'rating'     => $r->rating,
                'comment'    => $r->comment,
                'created_at' => $r->created_at?->format('d M Y'),
            ])->toArray();

        $avgRating = count($ratings) > 0
            ? round(collect($ratings)->avg('rating'), 1)
            : null;

        return response()->json([
            'coachId'   => $coachId,
            'coach'     => $coach ? [
                'name'      => $coach->name,
                'bio'       => $coach->bio,
                'photo_url' => $coach->photo_url,
                'city'      => $coach->city,
            ] : null,
            'ratings'   => $ratings,
            'avgRating' => $avgRating,
        ]);
    }

    /**
     * POST /api/v/client/coach-feedback
     */
    public function submitCoachFeedback(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Selecciona una calificación de 1 a 5 estrellas.',
            'rating.min'      => 'La calificación mínima es 1 estrella.',
            'rating.max'      => 'La calificación máxima es 5 estrellas.',
        ]);

        $coachId = DB::table('coach_messages')
            ->where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->value('coach_id');

        if (! $coachId) {
            return response()->json(['message' => 'No tienes un coach asignado.'], 422);
        }

        $recent = CoachRating::where('client_id', $client->id)
            ->where('coach_id', $coachId)
            ->where('created_at', '>=', now()->subDays(7))
            ->first();

        if ($recent) {
            $next = $recent->created_at->addDays(7)->format('d/m/Y');
            return response()->json([
                'message' => "Ya calificaste a tu coach esta semana. Podrás calificar nuevamente el {$next}.",
            ], 422);
        }

        $newRating = CoachRating::create([
            'client_id' => $client->id,
            'coach_id'  => $coachId,
            'rating'    => $validated['rating'],
            'comment'   => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'message' => '¡Valoración enviada! Gracias por tu feedback.',
            'rating'  => [
                'id'         => $newRating->id,
                'rating'     => $newRating->rating,
                'comment'    => $newRating->comment,
                'created_at' => $newRating->created_at->format('d M Y'),
            ],
        ]);
    }

    /**
     * POST /api/v/client/onboarding/complete
     *
     * Mark onboarding as completed for the current client.
     */
    public function completeOnboarding(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $client->update(['onboarding_completed' => true]);

        Cache::forget("dashboard:{$client->id}");

        return response()->json(['message' => 'Onboarding completado.']);
    }

    // ─── Ticket Support ──────────────────────────────────────────────────

    /**
     * GET /api/v/client/tickets
     */
    public function tickets(Request $request): JsonResponse
    {
        $client     = $this->resolveClientOrFail($request);
        $clientName = $client->name;
        $status     = $request->query('status', 'all');

        $query = Ticket::where('client_name', $clientName)->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $tickets = $query->get()->map(fn ($t) => [
            'id'          => $t->id,
            'ticket_type' => (string) $t->ticket_type,
            'description' => $t->description,
            'priority'    => $t->priority instanceof \App\Enums\TicketPriority ? $t->priority->value : (string) $t->priority,
            'status'      => $t->status instanceof \App\Enums\TicketStatus ? $t->status->value : (string) $t->status,
            'response'    => $t->response,
            'deadline'    => $t->deadline?->format('d M Y, H:i'),
            'resolved_at' => $t->resolved_at?->diffForHumans(),
            'created_at'  => $t->created_at?->diffForHumans(),
        ]);

        $statsRaw = Ticket::where('client_name', $clientName)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $stats = [
            'total'       => (int) array_sum($statsRaw),
            'open'        => (int) ($statsRaw['open'] ?? 0),
            'in_progress' => (int) ($statsRaw['in_progress'] ?? 0),
            'closed'      => (int) ($statsRaw['closed'] ?? 0),
        ];

        return response()->json(['tickets' => $tickets, 'stats' => $stats]);
    }

    /**
     * POST /api/v/client/tickets
     */
    public function createTicket(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'ticket_type' => 'required|in:rutina_nueva,cambio_rutina,nutricion,habitos,invitacion_cliente,otro',
            'description' => 'required|string|min:10|max:2000',
            'priority'    => 'required|in:normal,alta',
        ], [
            'ticket_type.required' => 'Selecciona el tipo de solicitud.',
            'ticket_type.in'       => 'Tipo de solicitud no válido.',
            'description.required' => 'La descripción es obligatoria.',
            'description.min'      => 'La descripción debe tener al menos 10 caracteres.',
        ]);

        $coachId = DB::table('coach_messages')
            ->where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->value('coach_id');

        $ticket = Ticket::create([
            'id'          => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => $client->name,
            'coach_id'    => $coachId !== null ? (string) $coachId : '',
            'ticket_type' => $validated['ticket_type'],
            'description' => $validated['description'],
            'priority'    => $validated['priority'],
            'status'      => 'open',
            'deadline'    => now()->addHours(48),
        ]);

        return response()->json([
            'message' => 'Solicitud enviada. Tu coach responderá en 48 horas.',
            'ticket'  => [
                'id'          => $ticket->id,
                'ticket_type' => (string) $ticket->ticket_type,
                'description' => $ticket->description,
                'priority'    => $ticket->priority instanceof \App\Enums\TicketPriority ? $ticket->priority->value : (string) $ticket->priority,
                'status'      => 'open',
                'response'    => null,
                'deadline'    => $ticket->deadline->format('d M Y, H:i'),
                'resolved_at' => null,
                'created_at'  => 'hace un momento',
            ],
        ], 201);
    }
}
