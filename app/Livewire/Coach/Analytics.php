<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\BiometricLog;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\CoachMessage;
use App\Models\Payment;
use App\Models\PersonalRecord;
use App\Models\TrainingLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Analytics'])]
class Analytics extends Component
{
    public string $dateRange = 'month';

    // Client Comparison Tool
    public array $selectedClients = [];
    public array $comparisonData = [];
    public bool $showComparison = false;
    public array $myClientsList = [];

    public function placeholder()
    {
        return view('livewire.placeholders.loading-skeleton');
    }

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

    // --- Enhanced Metrics ---

    // SLA breakdown
    public int $slaUnder12h = 0;
    public int $sla12to24h = 0;
    public int $sla24to48h = 0;
    public int $slaOver48h = 0;
    public string $slaGrade = 'N/A';

    // Adherence per client
    public array $adherenceByClient = [];

    // Training heatmap (last 4 weeks)
    public array $trainingHeatmap = [];
    public array $heatmapWeeks = [];

    // Biometric insights
    public float $avgWeightChange = 0;
    public float $avgBodyFat = 0;
    public float $avgSleepHours = 0;
    public int $clientsWithBiometrics = 0;

    // Coach Score (composite 0-100)
    public float $coachScore = 0;
    public string $coachScoreLabel = '';

    // At-risk clients
    public array $atRiskClients = [];

    protected Collection $coachClientIds;

    public function mount(): void
    {
        $this->loadMetrics();
        $this->loadMyClientsList();
    }

    public function switchDateRange(string $range): void
    {
        $this->dateRange = $range;
        $this->loadMetrics();
    }

    // ─── Client Comparison Tool ─────────────────────────────────────

    protected function loadMyClientsList(): void
    {
        $clientIds = $this->getCoachClientIds();

        $this->myClientsList = Client::whereIn('id', $clientIds)
            ->where('status', 'activo')
            ->orderBy('name')
            ->get(['id', 'name', 'plan', 'avatar_url'])
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'plan' => $c->plan?->value ?? 'sin plan',
                'initials' => collect(explode(' ', $c->name))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join(''),
            ])
            ->toArray();
    }

    public function toggleClientComparison(int $clientId): void
    {
        if (in_array($clientId, $this->selectedClients)) {
            $this->selectedClients = array_values(array_diff($this->selectedClients, [$clientId]));
        } else {
            // Max 4 clients
            if (count($this->selectedClients) >= 4) {
                return;
            }
            $this->selectedClients[] = $clientId;
        }

        if (count($this->selectedClients) >= 2) {
            $this->compareClients();
            $this->showComparison = true;
        } else {
            $this->comparisonData = [];
            $this->showComparison = false;
        }
    }

    public function clearComparison(): void
    {
        $this->selectedClients = [];
        $this->comparisonData = [];
        $this->showComparison = false;
    }

    public function compareClients(): void
    {
        if (count($this->selectedClients) < 2) {
            return;
        }

        $this->comparisonData = [];
        $colors = ['wc-accent', 'sky-500', 'violet-500', 'amber-500'];
        $dateFrom = $this->getDateFrom();

        foreach ($this->selectedClients as $idx => $clientId) {
            $client = Client::find($clientId);
            if (! $client) {
                continue;
            }

            // Latest biometric data
            $latestBio = BiometricLog::where('client_id', $clientId)
                ->orderByDesc('log_date')
                ->first();

            // First biometric (for delta calculations)
            $firstBio = BiometricLog::where('client_id', $clientId)
                ->when($dateFrom, fn($q) => $q->where('log_date', '>=', $dateFrom))
                ->orderBy('log_date')
                ->first();

            // Check-in stats in period
            $checkinQuery = Checkin::where('client_id', $clientId)
                ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom));

            $checkinCount = $checkinQuery->clone()->count();
            $avgBienestar = round((float) $checkinQuery->clone()->whereNotNull('bienestar')->avg('bienestar'), 1);
            $avgDias = round((float) $checkinQuery->clone()->whereNotNull('dias_entrenados')->avg('dias_entrenados'), 1);

            // Nutrition adherence
            $totalNutricion = $checkinQuery->clone()->whereNotNull('nutricion')->count();
            $siNutricion = $checkinQuery->clone()->where('nutricion', 'Si')->count();
            $nutritionRate = $totalNutricion > 0 ? round(($siNutricion / $totalNutricion) * 100, 1) : 0;

            // Training completion in period
            $trainingTotal = TrainingLog::where('client_id', $clientId)
                ->when($dateFrom, fn($q) => $q->where('log_date', '>=', $dateFrom))
                ->count();
            $trainingCompleted = TrainingLog::where('client_id', $clientId)
                ->where('completed', true)
                ->when($dateFrom, fn($q) => $q->where('log_date', '>=', $dateFrom))
                ->count();
            $trainingRate = $trainingTotal > 0 ? round(($trainingCompleted / $trainingTotal) * 100, 1) : 0;

            // Personal records count
            $prCount = PersonalRecord::where('client_id', $clientId)
                ->where('is_current', true)
                ->count();

            // Weight delta
            $weightDelta = null;
            if ($latestBio?->weight_kg && $firstBio?->weight_kg && $latestBio->id !== $firstBio->id) {
                $weightDelta = round($latestBio->weight_kg - $firstBio->weight_kg, 1);
            }

            // Body fat delta
            $bfDelta = null;
            if ($latestBio?->body_fat_pct && $firstBio?->body_fat_pct && $latestBio->id !== $firstBio->id) {
                $bfDelta = round($latestBio->body_fat_pct - $firstBio->body_fat_pct, 1);
            }

            // Avg sleep
            $avgSleep = round((float) BiometricLog::where('client_id', $clientId)
                ->when($dateFrom, fn($q) => $q->where('log_date', '>=', $dateFrom))
                ->whereNotNull('sleep_hours')
                ->where('sleep_hours', '>', 0)
                ->avg('sleep_hours'), 1);

            // Avg steps
            $avgSteps = (int) BiometricLog::where('client_id', $clientId)
                ->when($dateFrom, fn($q) => $q->where('log_date', '>=', $dateFrom))
                ->whereNotNull('steps')
                ->where('steps', '>', 0)
                ->avg('steps');

            $this->comparisonData[] = [
                'client_id' => $clientId,
                'name' => $client->name,
                'plan' => $client->plan?->value ?? 'sin plan',
                'color' => $colors[$idx] ?? 'gray-500',
                'initials' => collect(explode(' ', $client->name))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join(''),
                'weight_kg' => $latestBio?->weight_kg,
                'weight_delta' => $weightDelta,
                'body_fat_pct' => $latestBio?->body_fat_pct,
                'bf_delta' => $bfDelta,
                'waist_cm' => $latestBio?->waist_cm,
                'avg_bienestar' => $avgBienestar,
                'avg_dias' => $avgDias,
                'nutrition_rate' => $nutritionRate,
                'training_rate' => $trainingRate,
                'checkins' => $checkinCount,
                'pr_count' => $prCount,
                'avg_sleep' => $avgSleep,
                'avg_steps' => $avgSteps,
            ];
        }
    }

    // ─── End Comparison Tool ────────────────────────────────────────

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

        // Enhanced metrics
        $this->loadSlaBreakdown($dateFrom);
        $this->loadAdherenceByClient($dateFrom);
        $this->loadTrainingHeatmap();
        $this->loadBiometricInsights($dateFrom);
        $this->loadAtRiskClients($dateFrom);
        $this->calculateCoachScore();
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

    // ─── Enhanced Analytics Methods ────────────────────────────────

    protected function loadSlaBreakdown(?Carbon $dateFrom): void
    {
        try {
            $cacheKey = 'coach_sla_' . auth('wellcore')->id() . '_' . $this->dateRange;
            $sla = Cache::remember($cacheKey, 300, function () use ($dateFrom) {
                $replied = Checkin::whereIn('client_id', $this->coachClientIds)
                    ->whereNotNull('replied_at')
                    ->whereNotNull('created_at')
                    ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                    ->selectRaw('TIMESTAMPDIFF(HOUR, created_at, replied_at) as hours')
                    ->pluck('hours');

                return [
                    'under12' => $replied->filter(fn ($h) => $h < 12)->count(),
                    'h12to24' => $replied->filter(fn ($h) => $h >= 12 && $h < 24)->count(),
                    'h24to48' => $replied->filter(fn ($h) => $h >= 24 && $h < 48)->count(),
                    'over48' => $replied->filter(fn ($h) => $h >= 48)->count(),
                    'total' => $replied->count(),
                ];
            });

            $this->slaUnder12h = $sla['under12'];
            $this->sla12to24h = $sla['h12to24'];
            $this->sla24to48h = $sla['h24to48'];
            $this->slaOver48h = $sla['over48'];

            // SLA Grade based on % under 24h
            $total = $sla['total'];
            if ($total > 0) {
                $under24Pct = (($sla['under12'] + $sla['h12to24']) / $total) * 100;
                $this->slaGrade = match (true) {
                    $under24Pct >= 90 => 'A+',
                    $under24Pct >= 80 => 'A',
                    $under24Pct >= 70 => 'B',
                    $under24Pct >= 50 => 'C',
                    default => 'D',
                };
            }
        } catch (\Throwable) {
            // Graceful failure — keep defaults
        }
    }

    protected function loadAdherenceByClient(?Carbon $dateFrom): void
    {
        try {
            $cacheKey = 'coach_adherence_' . auth('wellcore')->id() . '_' . $this->dateRange;
            $this->adherenceByClient = Cache::remember($cacheKey, 300, function () use ($dateFrom) {
                $clients = Client::whereIn('id', $this->coachClientIds)
                    ->where('status', 'activo')
                    ->orderBy('name')
                    ->get(['id', 'name']);

                $weeksInPeriod = match ($this->dateRange) {
                    'month' => 4,
                    'quarter' => 13,
                    'year' => 52,
                    'all' => 4, // Default to 4 for "all"
                };

                $result = [];
                foreach ($clients as $client) {
                    $checkinCount = Checkin::where('client_id', $client->id)
                        ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                        ->count();

                    $avgBienestar = round((float) Checkin::where('client_id', $client->id)
                        ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                        ->whereNotNull('bienestar')
                        ->avg('bienestar'), 1);

                    $avgDias = round((float) Checkin::where('client_id', $client->id)
                        ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                        ->whereNotNull('dias_entrenados')
                        ->avg('dias_entrenados'), 1);

                    $trainingTotal = TrainingLog::where('client_id', $client->id)
                        ->when($dateFrom, fn ($q) => $q->where('log_date', '>=', $dateFrom))
                        ->count();
                    $trainingCompleted = TrainingLog::where('client_id', $client->id)
                        ->where('completed', true)
                        ->when($dateFrom, fn ($q) => $q->where('log_date', '>=', $dateFrom))
                        ->count();

                    $result[] = [
                        'name' => $client->name,
                        'checkin_count' => $checkinCount,
                        'expected' => $weeksInPeriod,
                        'avg_bienestar' => $avgBienestar,
                        'avg_dias' => $avgDias,
                        'training_total' => $trainingTotal,
                        'training_completed' => $trainingCompleted,
                        'training_rate' => $trainingTotal > 0 ? round(($trainingCompleted / $trainingTotal) * 100, 0) : 0,
                    ];
                }

                // Sort by checkin adherence descending
                usort($result, fn ($a, $b) => $b['checkin_count'] <=> $a['checkin_count']);

                return $result;
            });
        } catch (\Throwable) {
            $this->adherenceByClient = [];
        }
    }

    protected function loadTrainingHeatmap(): void
    {
        try {
            $cacheKey = 'coach_heatmap_' . auth('wellcore')->id();
            $heatmap = Cache::remember($cacheKey, 300, function () {
                $clients = Client::whereIn('id', $this->coachClientIds)
                    ->where('status', 'activo')
                    ->orderBy('name')
                    ->get(['id', 'name']);

                // Last 4 weeks: generate week labels (Mon-Sun)
                $weeks = [];
                for ($i = 3; $i >= 0; $i--) {
                    $weekStart = now()->subWeeks($i)->startOfWeek();
                    $weeks[] = [
                        'label' => $weekStart->format('d M'),
                        'start' => $weekStart->toDateString(),
                        'end' => $weekStart->copy()->endOfWeek()->toDateString(),
                        'year_num' => (int) $weekStart->format('o'),
                        'week_num' => (int) $weekStart->format('W'),
                    ];
                }

                $rows = [];
                foreach ($clients as $client) {
                    $weekData = [];
                    foreach ($weeks as $week) {
                        // Count training_logs completed for this week
                        $completed = TrainingLog::where('client_id', $client->id)
                            ->where('completed', true)
                            ->whereBetween('log_date', [$week['start'], $week['end']])
                            ->count();

                        $total = TrainingLog::where('client_id', $client->id)
                            ->whereBetween('log_date', [$week['start'], $week['end']])
                            ->count();

                        $weekData[] = [
                            'completed' => $completed,
                            'total' => $total,
                        ];
                    }
                    $rows[] = [
                        'name' => $client->name,
                        'weeks' => $weekData,
                    ];
                }

                return ['weeks' => $weeks, 'rows' => $rows];
            });

            $this->heatmapWeeks = $heatmap['weeks'];
            $this->trainingHeatmap = $heatmap['rows'];
        } catch (\Throwable) {
            $this->heatmapWeeks = [];
            $this->trainingHeatmap = [];
        }
    }

    protected function loadBiometricInsights(?Carbon $dateFrom): void
    {
        try {
            $cacheKey = 'coach_bio_' . auth('wellcore')->id() . '_' . $this->dateRange;
            $bio = Cache::remember($cacheKey, 300, function () use ($dateFrom) {
                // Clients with biometric data
                $clientsWithBio = BiometricLog::whereIn('client_id', $this->coachClientIds)
                    ->when($dateFrom, fn ($q) => $q->where('log_date', '>=', $dateFrom))
                    ->distinct('client_id')
                    ->count('client_id');

                // Average sleep
                $avgSleep = round((float) BiometricLog::whereIn('client_id', $this->coachClientIds)
                    ->when($dateFrom, fn ($q) => $q->where('log_date', '>=', $dateFrom))
                    ->whereNotNull('sleep_hours')
                    ->where('sleep_hours', '>', 0)
                    ->avg('sleep_hours'), 1);

                // Average body fat
                $avgBf = round((float) BiometricLog::whereIn('client_id', $this->coachClientIds)
                    ->when($dateFrom, fn ($q) => $q->where('log_date', '>=', $dateFrom))
                    ->whereNotNull('body_fat_pct')
                    ->where('body_fat_pct', '>', 0)
                    ->avg('body_fat_pct'), 1);

                // Average weight change per client
                $weightChanges = [];
                $clientIds = BiometricLog::whereIn('client_id', $this->coachClientIds)
                    ->whereNotNull('weight_kg')
                    ->where('weight_kg', '>', 0)
                    ->distinct()
                    ->pluck('client_id');

                foreach ($clientIds as $cid) {
                    $first = BiometricLog::where('client_id', $cid)
                        ->whereNotNull('weight_kg')
                        ->when($dateFrom, fn ($q) => $q->where('log_date', '>=', $dateFrom))
                        ->orderBy('log_date')
                        ->value('weight_kg');

                    $last = BiometricLog::where('client_id', $cid)
                        ->whereNotNull('weight_kg')
                        ->when($dateFrom, fn ($q) => $q->where('log_date', '>=', $dateFrom))
                        ->orderByDesc('log_date')
                        ->value('weight_kg');

                    if ($first && $last && $first != $last) {
                        $weightChanges[] = $last - $first;
                    }
                }

                $avgWeightChange = count($weightChanges) > 0
                    ? round(array_sum($weightChanges) / count($weightChanges), 1)
                    : 0;

                return compact('clientsWithBio', 'avgSleep', 'avgBf', 'avgWeightChange');
            });

            $this->clientsWithBiometrics = $bio['clientsWithBio'];
            $this->avgSleepHours = $bio['avgSleep'];
            $this->avgBodyFat = $bio['avgBf'];
            $this->avgWeightChange = $bio['avgWeightChange'];
        } catch (\Throwable) {
            // Keep defaults
        }
    }

    protected function loadAtRiskClients(?Carbon $dateFrom): void
    {
        try {
            $cacheKey = 'coach_risk_' . auth('wellcore')->id() . '_' . $this->dateRange;
            $this->atRiskClients = Cache::remember($cacheKey, 300, function () use ($dateFrom) {
                $clients = Client::whereIn('id', $this->coachClientIds)
                    ->where('status', 'activo')
                    ->get(['id', 'name', 'plan']);

                $risk = [];
                foreach ($clients as $client) {
                    $reasons = [];

                    // No check-ins in the last 14 days
                    $recentCheckins = Checkin::where('client_id', $client->id)
                        ->where('created_at', '>=', now()->subDays(14))
                        ->count();
                    if ($recentCheckins === 0) {
                        $reasons[] = 'Sin check-in hace 14+ dias';
                    }

                    // Low bienestar (avg < 5 in period)
                    $avgB = Checkin::where('client_id', $client->id)
                        ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                        ->whereNotNull('bienestar')
                        ->avg('bienestar');
                    if ($avgB !== null && $avgB < 5) {
                        $reasons[] = 'Bienestar bajo (' . round($avgB, 1) . '/10)';
                    }

                    // Low training (avg < 2 days/week)
                    $avgD = Checkin::where('client_id', $client->id)
                        ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                        ->whereNotNull('dias_entrenados')
                        ->avg('dias_entrenados');
                    if ($avgD !== null && $avgD < 2) {
                        $reasons[] = 'Entrenamiento bajo (' . round($avgD, 1) . ' dias/sem)';
                    }

                    if (! empty($reasons)) {
                        $risk[] = [
                            'name' => $client->name,
                            'plan' => $client->plan?->value ?? 'sin plan',
                            'reasons' => $reasons,
                            'severity' => count($reasons), // More reasons = higher risk
                        ];
                    }
                }

                // Sort by severity descending
                usort($risk, fn ($a, $b) => $b['severity'] <=> $a['severity']);

                return array_slice($risk, 0, 8);
            });
        } catch (\Throwable) {
            $this->atRiskClients = [];
        }
    }

    protected function calculateCoachScore(): void
    {
        try {
            // Composite score: weighted average of key metrics
            $scores = [];

            // Response time (25%): 100 if <12h, 80 if <24h, 50 if <48h, 20 otherwise
            if ($this->avgResponseHours > 0) {
                $scores['response'] = match (true) {
                    $this->avgResponseHours <= 12 => 100,
                    $this->avgResponseHours <= 24 => 80,
                    $this->avgResponseHours <= 48 => 50,
                    default => 20,
                };
            }

            // Reply rate (25%)
            $scores['reply'] = min(100, $this->checkinReplyRate);

            // Client retention (20%)
            $scores['retention'] = min(100, $this->retentionRate);

            // Client bienestar (15%): map 1-10 to 0-100
            if ($this->avgBienestar > 0) {
                $scores['bienestar'] = min(100, $this->avgBienestar * 10);
            }

            // Checkin completion (15%)
            $scores['completion'] = min(100, $this->checkinCompletionRate);

            // Weighted calculation
            $weights = [
                'response' => 0.25,
                'reply' => 0.25,
                'retention' => 0.20,
                'bienestar' => 0.15,
                'completion' => 0.15,
            ];

            $totalWeight = 0;
            $totalScore = 0;
            foreach ($weights as $key => $weight) {
                if (isset($scores[$key])) {
                    $totalScore += $scores[$key] * $weight;
                    $totalWeight += $weight;
                }
            }

            $this->coachScore = $totalWeight > 0 ? round($totalScore / $totalWeight, 0) : 0;

            $this->coachScoreLabel = match (true) {
                $this->coachScore >= 90 => 'Elite',
                $this->coachScore >= 75 => 'Excelente',
                $this->coachScore >= 60 => 'Bueno',
                $this->coachScore >= 40 => 'Regular',
                default => 'Necesita mejorar',
            };
        } catch (\Throwable) {
            $this->coachScore = 0;
            $this->coachScoreLabel = 'N/A';
        }
    }

    // ─── End Enhanced Analytics ──────────────────────────────────────

    public function render()
    {
        return view('livewire.coach.analytics');
    }
}
