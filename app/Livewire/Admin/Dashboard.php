<?php

namespace App\Livewire\Admin;

use App\Models\Checkin;
use App\Models\Client;
use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Referral;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin', ['title' => 'Dashboard'])]
class Dashboard extends Component
{
    // Summary stats
    public int $activeClients = 0;
    public string $monthlyRevenue = '0';
    public int $pendingCheckins = 0;
    public int $newInscriptions = 0;

    // Client breakdown
    public int $clientsActivo = 0;
    public int $clientsInactivo = 0;
    public int $clientsPendiente = 0;
    public int $clientsSuspendido = 0;
    public int $totalClients = 0;

    // Recent activity
    public array $recentInscriptions = [];
    public array $recentPayments = [];

    // Activity timeline
    public array $activityTimeline = [];
    public string $timelineFilter = 'todos';

    // Chart data
    public array $revenueChartData = [];
    public array $clientGrowthData = [];
    public array $planDistributionData = [];

    // Polling / refresh tracking
    public string $lastRefresh = '';

    public function mount(): void
    {
        $this->loadAllData();
    }

    /**
     * Called by wire:poll.30s — refreshes all dashboard data.
     */
    public function refreshStats(): void
    {
        Cache::forget('admin_dashboard_stats');
        $this->loadAllData();
    }

    /**
     * Filter the activity timeline by type.
     */
    public function filterTimeline(string $filter): void
    {
        $this->timelineFilter = $filter;
        $this->loadActivityTimeline();
    }

    protected function loadAllData(): void
    {
        $this->loadStats();
        $this->loadClientBreakdown();
        $this->loadRecentActivity();
        $this->loadActivityTimeline();
        $this->loadChartData();
        $this->lastRefresh = now()->format('h:i:s A');
    }

    protected function loadStats(): void
    {
        $stats = Cache::remember('admin_dashboard_stats', 300, function () {
            return [
                'activeClients' => Client::where('status', 'activo')->count(),
                'monthlyRevenue' => number_format(
                    (float) Payment::where('status', 'approved')
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->sum('amount'),
                    0, ',', '.'
                ),
                'pendingCheckins' => Checkin::whereNull('coach_reply')->count(),
                'newInscriptions' => Inscription::where('created_at', '>=', now()->startOfMonth())->count(),
            ];
        });

        $this->activeClients = $stats['activeClients'];
        $this->monthlyRevenue = $stats['monthlyRevenue'];
        $this->pendingCheckins = $stats['pendingCheckins'];
        $this->newInscriptions = $stats['newInscriptions'];
    }

    protected function loadClientBreakdown(): void
    {
        $breakdown = Client::selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $this->clientsActivo     = $breakdown->get('activo', 0);
        $this->clientsInactivo   = $breakdown->get('inactivo', 0);
        $this->clientsPendiente  = $breakdown->get('pendiente', 0);
        $this->clientsSuspendido = $breakdown->get('suspendido', 0);
        $this->totalClients      = $breakdown->sum();
    }

    protected function loadRecentActivity(): void
    {
        $this->recentInscriptions = Inscription::latest('created_at')
            ->take(5)
            ->get()
            ->map(fn ($i) => [
                'nombre' => trim(($i->nombre ?? '') . ' ' . ($i->apellido ?? '')),
                'email' => $i->email ?? '',
                'plan' => $i->plan?->label() ?? '-',
                'status' => $i->status ?? '-',
                'timeAgo' => $i->created_at?->diffForHumans() ?? '-',
            ])
            ->toArray();

        $this->recentPayments = Payment::with('client')
            ->where('status', 'approved')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function ($p) {
                // buyer_name / email may be NULL; fall back to the related client name
                $buyerName = $p->buyer_name
                    ?? $p->email
                    ?? $p->client?->name
                    ?? 'Sin nombre';

                // plan is cast to PlanType enum; if the stored value is unknown the
                // cast silently returns null — handle both cases defensively
                $planLabel = $p->plan instanceof \App\Enums\PlanType
                    ? $p->plan->label()
                    : (filled($p->getRawOriginal('plan'))
                        ? ucfirst($p->getRawOriginal('plan'))
                        : '-');

                return [
                    'buyerName' => $buyerName,
                    'plan'      => $planLabel,
                    'amount'    => number_format((float) $p->amount, 0, ',', '.'),
                    'method'    => $p->payment_method ?? '-',
                    'timeAgo'   => $p->created_at?->diffForHumans() ?? '-',
                ];
            })
            ->toArray();
    }

    protected function loadActivityTimeline(): void
    {
        $activities = collect();
        $filter = $this->timelineFilter;

        try {
            // Recent check-ins (last 7 days)
            if ($filter === 'todos' || $filter === 'checkin') {
                $checkins = DB::table('checkins')
                    ->join('clients', 'checkins.client_id', '=', 'clients.id')
                    ->where('checkins.created_at', '>=', now()->subDays(7))
                    ->select([
                        'checkins.created_at',
                        'clients.name as client_name',
                        'clients.avatar_url',
                        DB::raw("'checkin' as type"),
                        DB::raw('NULL as amount'),
                        DB::raw("CONCAT('Bienestar: ', checkins.bienestar, '/10') as detail"),
                    ])
                    ->orderByDesc('checkins.created_at')
                    ->limit(20)
                    ->get();
                $activities = $activities->merge($checkins);
            }

            // Training logs (last 7 days) — uses log_date, not created_at
            if ($filter === 'todos' || $filter === 'training') {
                $trainings = DB::table('training_logs')
                    ->join('clients', 'training_logs.client_id', '=', 'clients.id')
                    ->where('training_logs.log_date', '>=', now()->subDays(7)->toDateString())
                    ->where('training_logs.completed', 1)
                    ->select([
                        DB::raw("CAST(training_logs.log_date AS DATETIME) as created_at"),
                        'clients.name as client_name',
                        'clients.avatar_url',
                        DB::raw("'training' as type"),
                        DB::raw('NULL as amount'),
                        DB::raw("CONCAT('Semana ', training_logs.week_num) as detail"),
                    ])
                    ->orderByDesc('training_logs.log_date')
                    ->limit(20)
                    ->get();
                $activities = $activities->merge($trainings);
            }

            // Payments (last 30 days)
            if ($filter === 'todos' || $filter === 'payment') {
                $payments = DB::table('payments')
                    ->leftJoin('clients', 'payments.client_id', '=', 'clients.id')
                    ->where('payments.status', 'approved')
                    ->where('payments.created_at', '>=', now()->subDays(30))
                    ->select([
                        'payments.created_at',
                        DB::raw("COALESCE(clients.name, payments.buyer_name, payments.email) as client_name"),
                        'clients.avatar_url',
                        DB::raw("'payment' as type"),
                        'payments.amount',
                        DB::raw("CONCAT('Plan ', payments.plan) as detail"),
                    ])
                    ->orderByDesc('payments.created_at')
                    ->limit(20)
                    ->get();
                $activities = $activities->merge($payments);
            }

            // New client registrations (last 30 days)
            if ($filter === 'todos' || $filter === 'registration') {
                $registrations = DB::table('clients')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->select([
                        'created_at',
                        'name as client_name',
                        'avatar_url',
                        DB::raw("'registration' as type"),
                        DB::raw('NULL as amount'),
                        DB::raw("CONCAT('Plan ', plan) as detail"),
                    ])
                    ->orderByDesc('created_at')
                    ->limit(15)
                    ->get();
                $activities = $activities->merge($registrations);
            }

            // XP events (last 7 days)
            if ($filter === 'todos' || $filter === 'xp') {
                $xpEvents = DB::table('xp_events')
                    ->join('clients', DB::raw('CAST(xp_events.client_id AS UNSIGNED)'), '=', 'clients.id')
                    ->where('xp_events.created_at', '>=', now()->subDays(7))
                    ->select([
                        'xp_events.created_at',
                        'clients.name as client_name',
                        'clients.avatar_url',
                        DB::raw("'xp' as type"),
                        DB::raw('xp_events.xp_gained as amount'),
                        DB::raw("COALESCE(xp_events.description, xp_events.event_type) as detail"),
                    ])
                    ->orderByDesc('xp_events.created_at')
                    ->limit(15)
                    ->get();
                $activities = $activities->merge($xpEvents);
            }
        } catch (\Throwable $e) {
            Log::warning('Activity timeline query failed: ' . $e->getMessage());
        }

        $this->activityTimeline = $activities
            ->sortByDesc('created_at')
            ->take(30)
            ->values()
            ->map(fn ($item) => [
                'created_at' => $item->created_at,
                'client_name' => $item->client_name ?? 'Cliente',
                'avatar_url' => $item->avatar_url ?? null,
                'type' => $item->type,
                'amount' => $item->amount,
                'detail' => $item->detail ?? null,
                'time_ago' => Carbon::parse($item->created_at)->locale('es')->diffForHumans(),
            ])
            ->toArray();
    }

    protected function loadChartData(): void
    {
        // Revenue trend — last 6 months (payments table: status, amount, created_at)
        $this->revenueChartData = Cache::remember('admin_chart_revenue', 600, function () {
            return DB::table('payments')
                ->where('status', 'approved')
                ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total")
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(fn ($row) => ['month' => $row->month, 'total' => (float) $row->total])
                ->toArray();
        });

        // Client growth — last 6 months (clients table: created_at)
        $this->clientGrowthData = Cache::remember('admin_chart_client_growth', 600, function () {
            return DB::table('clients')
                ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(fn ($row) => ['month' => $row->month, 'count' => (int) $row->count])
                ->toArray();
        });

        // Plan distribution — active assigned plans by plan_type
        $this->planDistributionData = Cache::remember('admin_chart_plan_dist', 600, function () {
            return DB::table('assigned_plans')
                ->where('active', 1)
                ->selectRaw("plan_type as name, COUNT(*) as count")
                ->groupBy('plan_type')
                ->get()
                ->map(fn ($row) => ['name' => ucfirst($row->name ?? 'Sin tipo'), 'count' => (int) $row->count])
                ->toArray();
        });
    }

    public function render()
    {
        $pendingRewards = Referral::where('reward_granted', false)
            ->whereNotIn('status', ['denied'])
            ->with('referrer:id,name,email')
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('livewire.admin.dashboard', compact('pendingRewards'));
    }
}
