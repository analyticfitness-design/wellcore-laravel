<?php

namespace App\Livewire\Client;

use App\Models\Admin;
use App\Models\AssignedPlan;
use App\Models\BiometricLog;
use App\Models\Checkin;
use App\Models\ClientXp;
use App\Models\CoachMessage;
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
        $client = auth('wellcore')->user();

        // Greeting based on time of day
        $hour = (int) now()->format('H');
        if ($hour < 12) {
            $this->greeting = 'Buenos dias';
        } elseif ($hour < 18) {
            $this->greeting = 'Buenas tardes';
        } else {
            $this->greeting = 'Buenas noches';
        }

        $this->clientName = explode(' ', $client->name ?? 'Usuario')[0];
        $this->planLabel = $client->plan?->label();

        $this->loadStats($client);
        $this->loadWeeklyOverview($client);
        $this->loadRecentActivity($client);
        $this->loadPlanInfo($client);
        $this->loadDailyMissions($client);
        $this->loadCheckinCountdown($client);
        $this->loadWeeklySummary($client);
        $this->loadCoachInfo($client);
        $this->loadPlanProgress($client);
        $this->loadStreakCalendar($client);
        $this->loadWeightChart($client);

        // ITEM 4: Daily quote
        $this->dailyQuote = $this->getDailyQuote();
    }

    protected function loadStats($client): void
    {
        // Streak from client_xp table
        $xp = ClientXp::where('client_id', $client->id)->first();
        if ($xp) {
            $this->streakDays = $xp->streak_days ?? 0;
            $this->xpTotal = $xp->xp_total ?? 0;
            $this->level = $xp->level ?? 1;
        } else {
            $this->streakDays = 0;
            $this->xpTotal = 0;
            $this->level = 1;
        }

        // XP progress bar
        $levelCap = $this->level * 500;
        $this->xpForNextLevel = $levelCap;
        $this->xpProgress = $levelCap > 0
            ? (int) round(($this->xpTotal % $levelCap) / $levelCap * 100)
            : 0;

        // Check-ins this month
        $this->checkinsThisMonth = Checkin::where('client_id', $client->id)
            ->whereYear('checkin_date', now()->year)
            ->whereMonth('checkin_date', now()->month)
            ->count();

        // Days trained this week
        $this->trainedThisWeek = TrainingLog::where('client_id', $client->id)
            ->where('year_num', now()->year)
            ->where('week_num', now()->isoWeek())
            ->where('completed', true)
            ->count();
    }

    protected function loadWeeklyOverview($client): void
    {
        // Get training logs for current ISO week
        $logs = TrainingLog::where('client_id', $client->id)
            ->where('year_num', now()->year)
            ->where('week_num', now()->isoWeek())
            ->where('completed', true)
            ->pluck('log_date')
            ->map(fn ($d) => Carbon::parse($d)->dayOfWeekIso) // 1=Mon, 7=Sun
            ->toArray();

        $dayLabels = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
        $today = now()->dayOfWeekIso;

        $this->weekDays = [];
        for ($i = 1; $i <= 7; $i++) {
            $this->weekDays[] = [
                'label' => $dayLabels[$i - 1],
                'completed' => in_array($i, $logs),
                'isToday' => $i === $today,
            ];
        }
    }

    protected function loadRecentActivity($client): void
    {
        $activities = new Collection();

        // Recent training logs
        $trainingLogs = TrainingLog::where('client_id', $client->id)
            ->where('completed', true)
            ->orderByDesc('log_date')
            ->limit(5)
            ->get();

        foreach ($trainingLogs as $log) {
            $activities->push([
                'type' => 'training',
                'description' => 'Entrenamiento completado',
                'date' => Carbon::parse($log->log_date),
            ]);
        }

        // Recent check-ins
        $checkins = Checkin::where('client_id', $client->id)
            ->orderByDesc('checkin_date')
            ->limit(5)
            ->get();

        foreach ($checkins as $checkin) {
            $activities->push([
                'type' => 'checkin',
                'description' => 'Check-in semanal enviado',
                'date' => Carbon::parse($checkin->checkin_date),
            ]);
        }

        // Recent payments
        $payments = Payment::where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        foreach ($payments as $payment) {
            $activities->push([
                'type' => 'payment',
                'description' => 'Pago registrado — ' . ($payment->plan?->label() ?? 'Plan'),
                'date' => Carbon::parse($payment->created_at),
            ]);
        }

        // Sort by date descending and take top 5
        $this->recentActivity = $activities
            ->sortByDesc('date')
            ->take(5)
            ->map(fn ($item) => [
                'type' => $item['type'],
                'description' => $item['description'],
                'timeAgo' => $item['date']->diffForHumans(),
            ])
            ->values()
            ->toArray();
    }

    protected function loadPlanInfo($client): void
    {
        // Cache only a plain array (never the Eloquent model) to avoid
        // "incomplete object" errors after deploys when the cached serialized
        // model can no longer be deserialized into the current class.
        $planData = CacheFacade::remember(
            "client_plan_v2_{$client->id}",
            now()->addMinutes(5),
            fn () => AssignedPlan::where('client_id', $client->id)
                ->where('active', 1)
                ->orderByDesc('valid_from')
                ->select('plan_type', 'valid_from')
                ->first()
                ?->only(['plan_type', 'valid_from']) // plain array, never the model
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

    protected function loadDailyMissions($client): void
    {
        // Mission 1: completar entrenamiento hoy
        $trainedToday = TrainingLog::where('client_id', $client->id)
            ->where('log_date', now()->toDateString())
            ->where('completed', true)
            ->exists();

        // Mission 2: check-in esta semana
        $checkinThisWeek = Checkin::where('client_id', $client->id)
            ->whereBetween('checkin_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ])
            ->exists();

        // Mission 3: registrar peso esta semana
        $weightThisWeek = WeightLog::where('client_id', $client->id)
            ->where('week_number', now()->isoWeek())
            ->where('year', now()->year)
            ->exists();

        // Mission 4: revisar plan de nutricion (always available)
        $this->dailyMissions = [
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
                'completed' => false,
                'route'     => route('client.nutrition'),
                'icon'      => 'nutrition',
            ],
        ];
    }

    protected function loadCheckinCountdown($client): void
    {
        $lastCheckin = Checkin::where('client_id', $client->id)
            ->orderByDesc('checkin_date')
            ->first();

        if ($lastCheckin) {
            $nextDate = Carbon::parse($lastCheckin->checkin_date)->addDays(7);
            $this->daysUntilCheckin = (int) now()->startOfDay()->diffInDays($nextDate->startOfDay(), false);
            $this->nextCheckinDate = $nextDate->translatedFormat('l j M');
        } else {
            // No checkins yet — due today
            $this->daysUntilCheckin = 0;
            $this->nextCheckinDate = now()->translatedFormat('l j M');
        }
    }

    // ITEM 2: Weekly Summary — load last week's data
    protected function loadWeeklySummary($client): void
    {
        $lastIsoWeek = now()->subWeek()->isoWeek();
        $lastIsoYear = now()->subWeek()->isoWeekYear;
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        $this->lastWeekWorkouts = TrainingLog::where('client_id', $client->id)
            ->where('year_num', $lastIsoYear)
            ->where('week_num', $lastIsoWeek)
            ->where('completed', true)
            ->count();

        $this->lastWeekCheckins = Checkin::where('client_id', $client->id)
            ->whereBetween('checkin_date', [
                $lastWeekStart->toDateString(),
                $lastWeekEnd->toDateString(),
            ])
            ->count();

        // Get most recent weight from biometric_logs
        $latestWeight = BiometricLog::where('client_id', $client->id)
            ->whereNotNull('weight_kg')
            ->where('weight_kg', '>', 0)
            ->orderByDesc('log_date')
            ->first();

        $this->lastWeekWeight = $latestWeight ? number_format($latestWeight->weight_kg, 1) : null;

        $this->hasLastWeekData = ($this->lastWeekWorkouts > 0 || $this->lastWeekCheckins > 0);
    }

    // ITEM 3: Coach Info
    protected function loadCoachInfo($client): void
    {
        $coachId = AssignedPlan::where('client_id', $client->id)
            ->whereNotNull('assigned_by')
            ->orderByDesc('valid_from')
            ->value('assigned_by');

        if (! $coachId) {
            $coachId = CoachMessage::where('client_id', $client->id)
                ->whereNotNull('coach_id')
                ->orderByDesc('created_at')
                ->value('coach_id');
        }

        if ($coachId) {
            $coach = Admin::find($coachId);
            if ($coach && $coach->name) {
                $this->coachName = $coach->name;
                $parts = explode(' ', trim($coach->name));
                $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
                $this->coachInitials = $initials ?: 'WC';
            }
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

    // Streak Calendar — 90-day GitHub-style heatmap
    protected function loadStreakCalendar($client): void
    {
        $logs = TrainingLog::where('client_id', $client->id)
            ->where('completed', true)
            ->where('log_date', '>=', now()->subDays(90)->toDateString())
            ->selectRaw('DATE(log_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $this->streakCalendar = $logs;

        // Calculate consecutive days streak (counting back from today)
        $streak = 0;
        $checkDate = now()->copy();

        // If today has no log yet, start checking from yesterday
        if (! isset($logs[$checkDate->format('Y-m-d')])) {
            $checkDate->subDay();
        }

        while (isset($logs[$checkDate->format('Y-m-d')])) {
            $streak++;
            $checkDate->subDay();
        }

        $this->calendarStreak = $streak;
    }

    // Weight/Metrics Trend Chart — last 90 days from biometric_logs
    protected function loadWeightChart($client): void
    {
        $this->weightChartData = BiometricLog::where('client_id', $client->id)
            ->whereNotNull('weight_kg')
            ->where('weight_kg', '>', 0)
            ->where('log_date', '>=', now()->subDays(90)->toDateString())
            ->orderBy('log_date')
            ->get()
            ->map(fn ($log) => [
                'date' => Carbon::parse($log->log_date)->format('d M'),
                'weight' => round((float) $log->weight_kg, 1),
                'bodyFat' => $log->body_fat_pct ? round((float) $log->body_fat_pct, 1) : null,
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.client.dashboard');
    }
}
