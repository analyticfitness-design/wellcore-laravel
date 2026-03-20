<?php

namespace App\Livewire\Admin;

use App\Enums\PlanType;
use App\Models\Client;
use App\Models\Payment;
use App\Models\RiseDailyLog;
use App\Models\RiseHabitsLog;
use App\Models\RiseMeasurement;
use App\Models\RiseProgram;
use App\Models\RiseTracking;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'RISE Program'])]
class RiseManagement extends Component
{
    use WithPagination;

    public string $activeTab = 'overview';

    // Participants filters
    public string $search = '';
    public string $statusFilter = 'all';
    public string $sortBy = 'enrollment_date';
    public string $sortDir = 'desc';

    // Payments filters
    public string $paymentSearch = '';
    public string $paymentStatusFilter = 'all';

    // Participant detail modal
    public bool $showDetailModal = false;
    public ?int $detailProgramId = null;

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentStatusFilter(): void
    {
        $this->resetPage();
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'desc';
        }
    }

    public function viewParticipant(int $programId): void
    {
        $this->detailProgramId = $programId;
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailProgramId = null;
    }

    private function getOverviewStats(): array
    {
        $totalParticipants = RiseProgram::count();
        $activePrograms = RiseProgram::where('status', 'active')->count();
        $completedPrograms = RiseProgram::where('status', 'completed')->count();
        $completionRate = $totalParticipants > 0
            ? round(($completedPrograms / $totalParticipants) * 100, 1)
            : 0;

        $riseRevenue = Payment::where('plan', 'rise')
            ->where('status', 'approved')
            ->sum('amount');

        $totalRisePayments = Payment::where('plan', 'rise')->count();

        $recentEnrollments = RiseProgram::with('client')
            ->orderByDesc('enrollment_date')
            ->limit(5)
            ->get();

        $statusBreakdown = RiseProgram::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $dailyLogCount = RiseDailyLog::count();
        $measurementCount = RiseMeasurement::count();
        $trackingCount = RiseTracking::count();

        return [
            'totalParticipants' => $totalParticipants,
            'activePrograms' => $activePrograms,
            'completedPrograms' => $completedPrograms,
            'completionRate' => $completionRate,
            'riseRevenue' => $riseRevenue,
            'totalRisePayments' => $totalRisePayments,
            'recentEnrollments' => $recentEnrollments,
            'statusBreakdown' => $statusBreakdown,
            'dailyLogCount' => $dailyLogCount,
            'measurementCount' => $measurementCount,
            'trackingCount' => $trackingCount,
        ];
    }

    private function getProgressStats(): array
    {
        // Average measurements across all participants
        $avgMeasurements = RiseMeasurement::select([
            DB::raw('AVG(weight_kg) as avg_weight'),
            DB::raw('AVG(waist_cm) as avg_waist'),
            DB::raw('AVG(fat_pct) as avg_fat'),
            DB::raw('AVG(muscle_pct) as avg_muscle'),
            DB::raw('COUNT(DISTINCT client_id) as clients_measured'),
            DB::raw('COUNT(*) as total_entries'),
        ])->first();

        // Daily log completion rates
        $totalLogs = RiseDailyLog::count();
        $workoutCompleted = RiseDailyLog::where('workout_completed', true)->count();
        $workoutRate = $totalLogs > 0 ? round(($workoutCompleted / $totalLogs) * 100, 1) : 0;

        // Nutrition adherence breakdown
        $nutritionBreakdown = RiseDailyLog::select('nutrition_adherence', DB::raw('COUNT(*) as count'))
            ->whereNotNull('nutrition_adherence')
            ->groupBy('nutrition_adherence')
            ->pluck('count', 'nutrition_adherence')
            ->toArray();

        // Tracking averages
        $trackingAvg = RiseTracking::select([
            DB::raw('AVG(water_liters) as avg_water'),
            DB::raw('AVG(sleep_hours) as avg_sleep'),
            DB::raw('SUM(training_done) as training_done_count'),
            DB::raw('SUM(nutrition_done) as nutrition_done_count'),
            DB::raw('COUNT(*) as total_tracking'),
        ])->first();

        // Average mood/energy from daily logs
        $moodEnergyAvg = RiseDailyLog::select([
            DB::raw('AVG(mood_level) as avg_mood'),
            DB::raw('AVG(energy_level) as avg_energy'),
        ])->first();

        // Measurement deltas per client (first vs latest)
        $measurementDeltas = $this->calculateMeasurementDeltas();

        return [
            'avgMeasurements' => $avgMeasurements,
            'workoutRate' => $workoutRate,
            'totalLogs' => $totalLogs,
            'workoutCompleted' => $workoutCompleted,
            'nutritionBreakdown' => $nutritionBreakdown,
            'trackingAvg' => $trackingAvg,
            'moodEnergyAvg' => $moodEnergyAvg,
            'measurementDeltas' => $measurementDeltas,
        ];
    }

    private function calculateMeasurementDeltas(): array
    {
        $clients = RiseMeasurement::select('client_id')
            ->groupBy('client_id')
            ->havingRaw('COUNT(*) >= 2')
            ->pluck('client_id');

        if ($clients->isEmpty()) {
            return [];
        }

        $deltas = [];
        foreach ($clients as $clientId) {
            $first = RiseMeasurement::where('client_id', $clientId)
                ->orderBy('log_date')
                ->first();
            $latest = RiseMeasurement::where('client_id', $clientId)
                ->orderByDesc('log_date')
                ->first();

            if ($first && $latest && $first->id !== $latest->id) {
                $client = Client::find($clientId);
                $deltas[] = [
                    'client_name' => $client?->name ?? 'N/A',
                    'weight_delta' => $latest->weight_kg - $first->weight_kg,
                    'waist_delta' => $latest->waist_cm - $first->waist_cm,
                    'fat_delta' => $latest->fat_pct - $first->fat_pct,
                    'muscle_delta' => $latest->muscle_pct - $first->muscle_pct,
                ];
            }
        }

        return $deltas;
    }

    public function render()
    {
        $data = [
            'activeTab' => $this->activeTab,
        ];

        if ($this->activeTab === 'overview') {
            $data['stats'] = $this->getOverviewStats();
        }

        if ($this->activeTab === 'participants') {
            $query = RiseProgram::with('client')
                ->orderBy($this->sortBy, $this->sortDir);

            if ($this->statusFilter !== 'all') {
                $query->where('status', $this->statusFilter);
            }

            if ($this->search !== '') {
                $s = $this->search;
                $query->whereHas('client', function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%");
                });
            }

            $data['participants'] = $query->paginate(15);
        }

        if ($this->activeTab === 'progress') {
            $data['progress'] = $this->getProgressStats();
        }

        if ($this->activeTab === 'payments') {
            $query = Payment::with('client')
                ->where('plan', 'rise')
                ->orderByDesc('created_at');

            if ($this->paymentStatusFilter !== 'all') {
                $query->where('status', $this->paymentStatusFilter);
            }

            if ($this->paymentSearch !== '') {
                $s = $this->paymentSearch;
                $query->where(function ($q) use ($s) {
                    $q->where('buyer_name', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%")
                      ->orWhere('wompi_reference', 'like', "%{$s}%")
                      ->orWhere('payu_reference', 'like', "%{$s}%");
                });
            }

            $data['payments'] = $query->paginate(15);

            $data['paymentStats'] = [
                'total' => Payment::where('plan', 'rise')->count(),
                'totalRevenue' => Payment::where('plan', 'rise')->where('status', 'approved')->sum('amount'),
                'approved' => Payment::where('plan', 'rise')->where('status', 'approved')->count(),
                'pending' => Payment::where('plan', 'rise')->where('status', 'pending')->count(),
            ];
        }

        // Detail modal data
        if ($this->showDetailModal && $this->detailProgramId) {
            $program = RiseProgram::with('client', 'dailyLogs')->find($this->detailProgramId);
            if ($program) {
                $data['detailProgram'] = $program;
                $data['detailMeasurements'] = RiseMeasurement::where('client_id', $program->client_id)
                    ->orderByDesc('log_date')
                    ->limit(10)
                    ->get();
                $data['detailTracking'] = RiseTracking::where('client_id', $program->client_id)
                    ->orderByDesc('log_date')
                    ->limit(10)
                    ->get();
                $data['detailHabits'] = RiseHabitsLog::where('client_id', $program->client_id)
                    ->orderByDesc('log_date')
                    ->limit(10)
                    ->get();

                // Adherence calculation
                $programDays = $program->start_date && $program->end_date
                    ? $program->start_date->diffInDays(now()->min($program->end_date)) + 1
                    : 0;
                $loggedDays = $program->dailyLogs->count();
                $data['detailAdherence'] = $programDays > 0
                    ? round(($loggedDays / $programDays) * 100, 1)
                    : 0;
            }
        }

        return view('livewire.admin.rise-management', $data);
    }
}
