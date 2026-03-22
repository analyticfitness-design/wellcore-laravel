<?php

namespace App\Livewire\Client;

use App\Models\Admin;
use App\Models\AssignedPlan;
use App\Models\BiometricLog;
use App\Models\Checkin;
use App\Models\ClientXp;
use App\Models\CoachMessage;
use App\Models\HabitLog;
use App\Models\Payment;
use App\Models\TrainingLog;
use App\Models\WeightLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache as CacheFacade;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Dashboard'])]
class Dashboard extends Component
{
    // Greeting
    public string $greeting = '';
    public string $clientName = '';
    public ?string $planLabel = null;

    // Stats
    public int $streakDays = 0;
    public int $checkinsThisMonth = 0;
    public int $xpTotal = 0;
    public int $level = 0;
    public int $trainedThisWeek = 0;

    // Weekly overview
    public array $weekDays = [];

    // Recent activity
    public array $recentActivity = [];

    // Daily missions
    public array $dailyMissions = [];

    // XP progress
    public int $xpForNextLevel = 0;
    public int $xpProgress = 0;

    // Plan info
    public bool $hasActivePlan = false;
    public ?string $planPhase = null;
    public int $planDaysActive = 0;

    // Check-in countdown
    public int $daysUntilCheckin = 0;
    public string $nextCheckinDate = '';

    // ITEM 2: Weekly Summary
    public int $lastWeekWorkouts = 0;
    public int $lastWeekCheckins = 0;
    public ?string $lastWeekWeight = null;
    public bool $hasLastWeekData = false;

    // ITEM 3: Coach Avatar
    public string $coachName = 'Tu Coach WellCore';
    public string $coachInitials = 'WC';

    // ITEM 4: Motivational Quote
    public string $dailyQuote = '';

    // ITEM 5: Plan Progress Timeline
    public int $weeksActive = 0;
    public int $totalWeeks = 12;
    public int $progressPercent = 0;
    public ?string $startDate = null;

    // Streak Calendar (90 days)
    public array $streakCalendar = [];
    public int $calendarStreak = 0;

    // Weight/Metrics Chart
    public array $weightChartData = [];

    public function mount(): void
    {
        $client   = auth('wellcore')->user();
        $clientId = $client->id;

        // Greeting based on time of day (not cached — depends on current hour)
        $hour = (int) now()->format('H');
        if ($hour < 12) {
            $this->greeting = 'Buenos dias';
        } elseif ($hour < 18) {
            $this->greeting = 'Buenas tardes';
        } else {
            $this->greeting = 'Buenas noches';
        }

        $this->clientName = explode(' ', $client->name ?? 'Usuario')[0];
        try {
            $this->planLabel = $client->plan?->label();
        } catch (\ValueError) {
            $this->planLabel = 'Plan';
        }

        // ---------- L2 cache: all DB-heavy data, 5-minute TTL ----------
        // loadPlanInfo()     → excluded: has its own Cache::remember (client_plan_v3_*)
        // loadPlanProgress() → excluded: reads client->created_at only, zero DB queries
        // getDailyQuote()    → excluded: pure computation, deterministic per day-of-year
        $cached = CacheFacade::remember("dashboard:{$clientId}", 300, function () use ($clientId, $client) {

            // --- Stats ---
            $xp            = ClientXp::where('client_id', $clientId)->first();
            $streakDays    = $xp?->streak_days ?? 0;
            $xpTotal       = $xp?->xp_total ?? 0;
            $level         = $xp?->level ?? 1;
            // XP formula (must match WorkoutPlayer): level = floor(xp / 200) + 1
            // Recompute level from raw XP so the progress bar is always consistent,
            // even if the cached $level column is stale.
            $currentLevel     = (int) floor($xpTotal / 200) + 1;
            $xpForCurrentLevel = ($currentLevel - 1) * 200; // XP at start of this level
            $xpForNext        = $currentLevel * 200;         // XP at start of next level
            $xpProgress       = ($xpForNext > $xpForCurrentLevel)
                ? (int) round(($xpTotal - $xpForCurrentLevel) / ($xpForNext - $xpForCurrentLevel) * 100)
                : 100;
            $checkinsMonth = Checkin::where('client_id', $clientId)
                ->whereYear('checkin_date', now()->year)
                ->whereMonth('checkin_date', now()->month)
                ->count();
            $trainedWeek   = TrainingLog::where('client_id', $clientId)
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

            $dayLabels   = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
            $today       = now()->dayOfWeekIso;
            $weekDays    = [];
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

            // --- Daily missions ---
            $trainedToday    = TrainingLog::where('client_id', $clientId)
                ->where('log_date', now()->toDateString())
                ->where('completed', true)
                ->exists();
            $checkinThisWeek = Checkin::where('client_id', $clientId)
                ->whereBetween('checkin_date', [
                    now()->startOfWeek()->toDateString(),
                    now()->endOfWeek()->toDateString(),
                ])
                ->exists();
            $weightThisWeek  = WeightLog::where('client_id', $clientId)
                ->where('week_number', now()->isoWeek())
                ->where('year', now()->year)
                ->exists();
            $nutritionToday  = HabitLog::where('client_id', $clientId)
                ->where('habit_type', 'nutricion')
                ->whereDate('log_date', today())
                ->where('value', '>=', 1)
                ->exists();

            $dailyMissions = [
                [
                    'key'       => 'training',
                    'title'     => 'Completar entrenamiento',
                    'completed' => $trainedToday,
                    'route'     => route('client.training'),
                    'icon'      => 'dumbbell',
                ],
                [
                    'key'       => 'checkin',
                    'title'     => 'Hacer check-in semanal',
                    'completed' => $checkinThisWeek,
                    'route'     => route('client.checkin'),
                    'icon'      => 'checkin',
                ],
                [
                    'key'       => 'weight',
                    'title'     => 'Registrar peso',
                    'completed' => $weightThisWeek,
                    'route'     => route('client.metrics'),
                    'icon'      => 'scale',
                ],
                [
                    'key'       => 'nutrition',
                    'title'     => 'Revisar plan de nutricion',
                    'completed' => $nutritionToday,
                    'route'     => route('client.nutrition'),
                    'icon'      => 'nutrition',
                ],
            ];

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
            $lastIsoWeek    = now()->subWeek()->isoWeek();
            $lastIsoYear    = now()->subWeek()->isoWeekYear;
            $lastWeekStart  = now()->subWeek()->startOfWeek()->toDateString();
            $lastWeekEnd    = now()->subWeek()->endOfWeek()->toDateString();

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
                'streakDays'       => $streakDays,
                'xpTotal'          => $xpTotal,
                'level'            => $level,
                'xpForNextLevel'   => $xpForNext,
                'xpProgress'       => $xpProgress,
                'checkinsThisMonth'=> $checkinsMonth,
                'trainedThisWeek'  => $trainedWeek,
                'weekDays'         => $weekDays,
                'recentActivity'   => $recentActivity,
                'dailyMissions'    => $dailyMissions,
                'daysUntilCheckin' => $daysUntil,
                'nextCheckinDate'  => $nextCheckinStr,
                'lastWeekWorkouts' => $lastWeekWorkouts,
                'lastWeekCheckins' => $lastWeekCheckins,
                'lastWeekWeight'   => $lastWeekWeight,
                'coachName'        => $coachName,
                'coachInitials'    => $coachInitials,
                'streakCalendar'   => $calendarLogs,
                'calendarStreak'   => $streak,
                'weightChartData'  => $weightChartData,
            ];
        });

        // Assign cached scalars and arrays to component properties
        $this->streakDays        = $cached['streakDays'];
        $this->xpTotal           = $cached['xpTotal'];
        $this->level             = $cached['level'];
        $this->xpForNextLevel    = $cached['xpForNextLevel'];
        $this->xpProgress        = $cached['xpProgress'];
        $this->checkinsThisMonth = $cached['checkinsThisMonth'];
        $this->trainedThisWeek   = $cached['trainedThisWeek'];
        $this->weekDays          = $cached['weekDays'];
        $this->recentActivity    = $cached['recentActivity'];
        $this->dailyMissions     = $cached['dailyMissions'];
        $this->daysUntilCheckin  = $cached['daysUntilCheckin'];
        $this->nextCheckinDate   = $cached['nextCheckinDate'];
        $this->lastWeekWorkouts  = $cached['lastWeekWorkouts'];
        $this->lastWeekCheckins  = $cached['lastWeekCheckins'];
        $this->lastWeekWeight    = $cached['lastWeekWeight'];
        $this->hasLastWeekData   = ($cached['lastWeekWorkouts'] > 0 || $cached['lastWeekCheckins'] > 0);
        $this->coachName         = $cached['coachName'];
        $this->coachInitials     = $cached['coachInitials'];
        $this->streakCalendar    = $cached['streakCalendar'];
        $this->calendarStreak    = $cached['calendarStreak'];
        $this->weightChartData   = $cached['weightChartData'];
        // ---------- end L2 cache ----------

        // These run outside the cache (no DB queries or already cached separately)
        $this->loadPlanInfo($client);    // has its own cache: client_plan_v3_{id}
        $this->loadPlanProgress($client); // reads client->created_at only
        $this->dailyQuote = $this->getDailyQuote(); // pure computation
    }

    protected function loadPlanInfo($client): void
    {
        // Cache only a plain array (never the Eloquent model) to avoid
        // "incomplete object" errors after deploys when the cached serialized
        // model can no longer be deserialized into the current class.
        // v3: force valid_from to string so Carbon objects never reach the cache
        $planData = CacheFacade::remember(
            "client_plan_v3_{$client->id}",
            now()->addMinutes(5),
            function () use ($client) {
                $plan = AssignedPlan::where('client_id', $client->id)
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
            }
        );

        if ($planData && isset($planData['plan_type'])) {
            $this->hasActivePlan = true;
            $this->planPhase = $planData['plan_type'];
            $this->planDaysActive = (int) Carbon::parse($planData['valid_from'])->diffInDays(now());
        } else {
            $this->hasActivePlan = false;
            $this->planPhase = null;
            $this->planDaysActive = 0;
        }
    }

    // ITEM 4: Daily Motivational Quote
    private function getDailyQuote(): string
    {
        $quotes = [
            'La disciplina es el puente entre tus metas y tus logros.',
            'No entrenas para ser perfecto, entrenas para ser mejor.',
            'Tu cuerpo puede soportar casi todo. Es tu mente la que debes convencer.',
            'El dolor de hoy es la fuerza de mañana.',
            'No se trata de ser el mejor. Se trata de ser mejor que ayer.',
            'La constancia supera al talento cuando el talento no es constante.',
            'Cada repetición cuenta. Cada decisión cuenta.',
            'El éxito no se mide en kilos, se mide en hábitos.',
            'Entrena porque amas tu cuerpo, no porque lo odies.',
            'El progreso no siempre es lineal, pero siempre vale la pena.',
            'Hoy es un buen dia para entrenar.',
            'Tu versión futura te agradecerá lo que hagas hoy.',
            'No hay atajos. Solo hay proceso.',
            'La ciencia no miente. La consistencia no falla.',
            'Menos excusas, mas evidencia.',
            'El método funciona cuando tú funcionas con él.',
            'Descansa, pero no renuncies.',
            'Cada check-in es un paso hacia tu mejor versión.',
            'La transformación empieza cuando dejas de buscar la perfección.',
            'Confiar en el proceso es parte del proceso.',
            'Tu coach está aquí. Tu comunidad está aquí. Solo falta tu decisión.',
            'No necesitas motivacion. Necesitas un sistema.',
            'El gym no te cambia. Los hábitos sí.',
            'Lo que mides, mejora.',
            'Sin prisa, pero sin pausa.',
            'Hoy es día de construir.',
            'Cada gota de sudor es una inversion.',
            'La ciencia respalda tu esfuerzo.',
            'No es sobre el peso en la barra. Es sobre el peso que cargas menos.',
            'Esto no es una dieta. Es tu nueva forma de vivir.',
        ];

        return $quotes[now()->dayOfYear % count($quotes)];
    }

    // ITEM 5: Plan Progress Timeline
    protected function loadPlanProgress($client): void
    {
        $createdAt = $client->created_at ?? $client->fecha_inicio ?? now();
        $this->startDate = Carbon::parse($createdAt)->format('d M Y');
        $this->weeksActive = (int) max(1, ceil(Carbon::parse($createdAt)->diffInWeeks(now())));
        $this->totalWeeks = 12;
        $this->progressPercent = (int) min(100, ($this->weeksActive / $this->totalWeeks) * 100);
    }

    public function render()
    {
        return view('livewire.client.dashboard');
    }
}
