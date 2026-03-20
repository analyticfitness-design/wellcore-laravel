<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\CoachMessage;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Analytics'])]
class Analytics extends Component
{
    public string $dateRange = 'month';

    // Client Overview
    public int $totalClients = 0;
    public int $activeClients = 0;
    public int $inactiveClients = 0;
    public float $retentionRate = 0;
    public string $retentionTrend = 'neutral'; // up, down, neutral

    // Response Performance
    public float $avgResponseHours = 0;
    public float $checkinReplyRate = 0;
    public int $totalCheckins = 0;
    public int $repliedCheckins = 0;
    public string $responseTrend = 'neutral';

    // Checkin Completion
    public float $checkinCompletionRate = 0;
    public int $expectedCheckins = 0;
    public int $actualCheckins = 0;

    // Client Progress
    public float $avgBienestar = 0;
    public float $avgDiasEntrenados = 0;
    public float $nutritionAdherenceRate = 0;
    public string $bienestarTrend = 'neutral';
    public string $trainingTrend = 'neutral';

    // Messages
    public int $messagesSent = 0;
    public int $messagesReceived = 0;
    public int $totalMessages = 0;

    // Revenue
    public float $totalRevenue = 0;
    public float $monthlyRevenue = 0;
    public int $payingClients = 0;
    public string $revenueTrend = 'neutral';

    // Bienestar trend data (last 8 weeks)
    public array $bienestarChart = [];

    // Top clients by training adherence
    public array $topClients = [];

    // Plan distribution
    public array $planDistribution = [];

    // Monthly revenue chart (last 6 months)
    public array $revenueChart = [];

    // Message activity chart (last 8 weeks)
    public array $messageChart = [];

    protected Collection $coachClientIds;

    public function mount(): void
    {
        $this->loadMetrics();
    }

    public function switchDateRange(string $range): void
    {
        $this->dateRange = $range;
        $this->loadMetrics();
    }

    protected function getCoachClientIds(): Collection
    {
        $coachId = auth('wellcore')->id();

        return AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();
    }

    protected function getDateFrom(): ?Carbon
    {
        return match ($this->dateRange) {
            'month' => now()->subMonth(),
            'quarter' => now()->subMonths(3),
            'year' => now()->subYear(),
            'all' => null,
        };
    }

    protected function loadMetrics(): void
    {
        $this->coachClientIds = $this->getCoachClientIds();
        $dateFrom = $this->getDateFrom();

        if ($this->coachClientIds->isEmpty()) {
            return;
        }

        $this->loadClientOverview();
        $this->loadResponsePerformance($dateFrom);
        $this->loadCheckinCompletion($dateFrom);
        $this->loadClientProgress($dateFrom);
        $this->loadMessageActivity($dateFrom);
        $this->loadRevenue($dateFrom);
        $this->loadBienestarChart();
        $this->loadTopClients($dateFrom);
        $this->loadPlanDistribution();
        $this->loadRevenueChart();
        $this->loadMessageChart();
    }

    protected function loadClientOverview(): void
    {
        $clients = Client::whereIn('id', $this->coachClientIds)->get();

        $this->totalClients = $clients->count();
        $this->activeClients = $clients->where('status', 'activo')->count();
        $this->inactiveClients = $this->totalClients - $this->activeClients;

        $this->retentionRate = $this->totalClients > 0
            ? round(($this->activeClients / $this->totalClients) * 100, 1)
            : 0;

        // Trend: compare with previous period
        $previousActive = Client::whereIn('id', $this->coachClientIds)
            ->where('status', 'activo')
            ->where('fecha_inicio', '<', now()->subMonth())
            ->count();

        if ($previousActive > 0) {
            $this->retentionTrend = $this->activeClients >= $previousActive ? 'up' : 'down';
        }
    }

    protected function loadResponsePerformance(?Carbon $dateFrom): void
    {
        $query = Checkin::whereIn('client_id', $this->coachClientIds);

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        $this->totalCheckins = $query->count();

        $repliedQuery = clone $query;
        $this->repliedCheckins = $repliedQuery->whereNotNull('coach_reply')->count();

        $this->checkinReplyRate = $this->totalCheckins > 0
            ? round(($this->repliedCheckins / $this->totalCheckins) * 100, 1)
            : 0;

        // Average response time in hours
        $avgSeconds = Checkin::whereIn('client_id', $this->coachClientIds)
            ->whereNotNull('replied_at')
            ->whereNotNull('created_at')
            ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, replied_at)) as avg_seconds')
            ->value('avg_seconds');

        $this->avgResponseHours = $avgSeconds ? round($avgSeconds / 3600, 1) : 0;

        // Trend: compare with previous period
        $prevFrom = $dateFrom ? (clone $dateFrom)->subMonth() : null;
        $prevAvg = Checkin::whereIn('client_id', $this->coachClientIds)
            ->whereNotNull('replied_at')
            ->whereNotNull('created_at')
            ->when($prevFrom, fn($q) => $q->where('created_at', '>=', $prevFrom))
            ->when($dateFrom, fn($q) => $q->where('created_at', '<', $dateFrom))
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, replied_at)) as avg_seconds')
            ->value('avg_seconds');

        if ($prevAvg && $avgSeconds) {
            // Lower response time is better
            $this->responseTrend = $avgSeconds <= $prevAvg ? 'up' : 'down';
        }
    }

    protected function loadCheckinCompletion(?Carbon $dateFrom): void
    {
        // Calculate expected checkins: 1 per active client per week
        $weeksInPeriod = match ($this->dateRange) {
            'month' => 4,
            'quarter' => 13,
            'year' => 52,
            'all' => max(1, (int) ceil(now()->diffInWeeks(
                Client::whereIn('id', $this->coachClientIds)->min('fecha_inicio') ?? now()
            ))),
        };

        $this->expectedCheckins = $this->activeClients * $weeksInPeriod;

        $query = Checkin::whereIn('client_id', $this->coachClientIds);
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        $this->actualCheckins = $query->count();

        $this->checkinCompletionRate = $this->expectedCheckins > 0
            ? round(min(100, ($this->actualCheckins / $this->expectedCheckins) * 100), 1)
            : 0;
    }

    protected function loadClientProgress(?Carbon $dateFrom): void
    {
        $query = Checkin::whereIn('client_id', $this->coachClientIds);

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        // Average bienestar (1-10 scale)
        $this->avgBienestar = round((float) $query->clone()->whereNotNull('bienestar')->avg('bienestar'), 1);

        // Average training days
        $this->avgDiasEntrenados = round((float) $query->clone()->whereNotNull('dias_entrenados')->avg('dias_entrenados'), 1);

        // Nutrition adherence: "Si" answers vs total
        $totalNutricion = $query->clone()->whereNotNull('nutricion')->count();
        $siNutricion = $query->clone()->where('nutricion', 'Si')->count();
        $this->nutritionAdherenceRate = $totalNutricion > 0
            ? round(($siNutricion / $totalNutricion) * 100, 1)
            : 0;

        // Bienestar trend: current vs previous period
        $prevFrom = $dateFrom ? (clone $dateFrom)->subMonth() : null;
        $prevBienestar = Checkin::whereIn('client_id', $this->coachClientIds)
            ->whereNotNull('bienestar')
            ->when($prevFrom, fn($q) => $q->where('created_at', '>=', $prevFrom))
            ->when($dateFrom, fn($q) => $q->where('created_at', '<', $dateFrom))
            ->avg('bienestar');

        if ($prevBienestar) {
            $this->bienestarTrend = $this->avgBienestar >= $prevBienestar ? 'up' : 'down';
        }

        // Training trend
        $prevDias = Checkin::whereIn('client_id', $this->coachClientIds)
            ->whereNotNull('dias_entrenados')
            ->when($prevFrom, fn($q) => $q->where('created_at', '>=', $prevFrom))
            ->when($dateFrom, fn($q) => $q->where('created_at', '<', $dateFrom))
            ->avg('dias_entrenados');

        if ($prevDias) {
            $this->trainingTrend = $this->avgDiasEntrenados >= $prevDias ? 'up' : 'down';
        }
    }

    protected function loadMessageActivity(?Carbon $dateFrom): void
    {
        $coachId = auth('wellcore')->id();

        $query = CoachMessage::where('coach_id', $coachId);

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        $this->messagesSent = $query->clone()->where('direction', 'coach_to_client')->count();
        $this->messagesReceived = $query->clone()->where('direction', 'client_to_coach')->count();
        $this->totalMessages = $this->messagesSent + $this->messagesReceived;
    }

    protected function loadRevenue(?Carbon $dateFrom): void
    {
        $query = Payment::whereIn('client_id', $this->coachClientIds)
            ->where('status', 'approved');

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        $this->totalRevenue = round((float) $query->sum('amount'), 0);

        // Monthly revenue (current month)
        $this->monthlyRevenue = round((float) Payment::whereIn('client_id', $this->coachClientIds)
            ->where('status', 'approved')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount'), 0);

        // Paying clients in period
        $this->payingClients = Payment::whereIn('client_id', $this->coachClientIds)
            ->where('status', 'approved')
            ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
            ->distinct('client_id')
            ->count('client_id');

        // Revenue trend: current month vs last month
        $lastMonthRevenue = Payment::whereIn('client_id', $this->coachClientIds)
            ->where('status', 'approved')
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('amount');

        $this->revenueTrend = $this->monthlyRevenue >= $lastMonthRevenue ? 'up' : 'down';
        if ($lastMonthRevenue == 0 && $this->monthlyRevenue == 0) {
            $this->revenueTrend = 'neutral';
        }
    }

    protected function loadBienestarChart(): void
    {
        $this->bienestarChart = [];

        for ($i = 7; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();

            $avg = Checkin::whereIn('client_id', $this->coachClientIds)
                ->whereNotNull('bienestar')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->avg('bienestar');

            $this->bienestarChart[] = [
                'label' => $weekStart->format('d M'),
                'value' => round((float) $avg, 1),
            ];
        }
    }

    protected function loadTopClients(?Carbon $dateFrom): void
    {
        $this->topClients = [];

        $clients = Client::whereIn('id', $this->coachClientIds)
            ->where('status', 'activo')
            ->get();

        $clientStats = [];
        foreach ($clients as $client) {
            $query = Checkin::where('client_id', $client->id);
            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }

            $avgDias = $query->clone()->whereNotNull('dias_entrenados')->avg('dias_entrenados');
            $avgBienestar = $query->clone()->whereNotNull('bienestar')->avg('bienestar');
            $checkinCount = $query->count();

            if ($checkinCount === 0) {
                continue;
            }

            $clientStats[] = [
                'name' => $client->name,
                'plan' => $client->plan?->value ?? 'sin plan',
                'avg_dias' => round((float) $avgDias, 1),
                'avg_bienestar' => round((float) $avgBienestar, 1),
                'checkins' => $checkinCount,
            ];
        }

        // Sort by avg_dias descending
        usort($clientStats, fn($a, $b) => $b['avg_dias'] <=> $a['avg_dias']);

        $this->topClients = array_slice($clientStats, 0, 5);
    }

    protected function loadPlanDistribution(): void
    {
        $this->planDistribution = [];

        $clients = Client::whereIn('id', $this->coachClientIds)
            ->whereNotNull('plan')
            ->get()
            ->groupBy(fn($c) => $c->plan?->value ?? 'otro');

        foreach ($clients as $plan => $group) {
            $this->planDistribution[] = [
                'plan' => ucfirst($plan),
                'count' => $group->count(),
                'percentage' => $this->totalClients > 0
                    ? round(($group->count() / $this->totalClients) * 100, 1)
                    : 0,
            ];
        }

        // Sort by count descending
        usort($this->planDistribution, fn($a, $b) => $b['count'] <=> $a['count']);
    }

    protected function loadRevenueChart(): void
    {
        $this->revenueChart = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = Payment::whereIn('client_id', $this->coachClientIds)
                ->where('status', 'approved')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');

            $this->revenueChart[] = [
                'label' => $month->translatedFormat('M Y'),
                'value' => round((float) $revenue, 0),
            ];
        }
    }

    protected function loadMessageChart(): void
    {
        $coachId = auth('wellcore')->id();
        $this->messageChart = [];

        for ($i = 7; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();

            $sent = CoachMessage::where('coach_id', $coachId)
                ->where('direction', 'coach_to_client')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();

            $received = CoachMessage::where('coach_id', $coachId)
                ->where('direction', 'client_to_coach')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();

            $this->messageChart[] = [
                'label' => $weekStart->format('d M'),
                'sent' => $sent,
                'received' => $received,
            ];
        }
    }

    public function render()
    {
        return view('livewire.coach.analytics');
    }
}
