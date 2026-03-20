<?php

namespace App\Livewire\Client;

use App\Models\AssignedPlan;
use App\Models\Checkin;
use App\Models\ClientXp;
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
        $plan = CacheFacade::remember(
            "client_plan_{$client->id}",
            now()->addMinutes(5),
            fn () => AssignedPlan::where('client_id', $client->id)
                ->where('active', 1)
                ->orderByDesc('valid_from')
                ->first()
        );

        if ($plan) {
            $this->hasActivePlan = true;
            $this->planPhase = $plan->plan_type;
            $this->planDaysActive = (int) Carbon::parse($plan->valid_from)->diffInDays(now());
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

    public function render()
    {
        return view('livewire.client.dashboard');
    }
}
