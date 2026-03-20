<?php

namespace App\Livewire\Rise;

use App\Models\RiseDailyLog;
use App\Models\RiseMeasurement;
use App\Models\RiseProgram;
use App\Models\RiseTracking;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rise', ['title' => 'Dashboard'])]
class Dashboard extends Component
{
    // Greeting
    public string $greeting = '';
    public string $clientName = '';

    // Program info
    public bool $hasProgram = false;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public int $totalDays = 84;
    public int $daysElapsed = 0;
    public int $daysRemaining = 0;
    public float $progressPct = 0;
    public int $currentWeek = 1;

    // Weekly summary
    public int $workoutsThisWeek = 0;
    public int $nutritionDaysThisWeek = 0;
    public int $habitsCompletedThisWeek = 0;

    // Streak
    public int $currentStreak = 0;

    // Quick stats
    public ?float $latestWeight = null;
    public ?float $weightChange = null;
    public int $totalTrackingDays = 0;
    public float $overallAdherence = 0;

    // Weekly tracking grid
    public array $weekDays = [];

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

        $this->loadProgram($client);
        $this->loadWeeklySummary($client);
        $this->loadStreak($client);
        $this->loadQuickStats($client);
        $this->loadWeeklyGrid($client);
    }

    protected function loadProgram($client): void
    {
        $program = RiseProgram::where('client_id', $client->id)
            ->where('status', 'active')
            ->first();

        if (! $program) {
            return;
        }

        $this->hasProgram = true;
        $this->startDate = $program->start_date?->format('d M Y');
        $this->endDate = $program->end_date?->format('d M Y');
        $this->totalDays = $program->start_date && $program->end_date
            ? Carbon::parse($program->start_date)->diffInDays($program->end_date)
            : 84;
        $this->daysElapsed = $program->start_date
            ? max(0, Carbon::parse($program->start_date)->diffInDays(now()))
            : 0;
        $this->daysRemaining = max(0, $this->totalDays - $this->daysElapsed);
        $this->progressPct = $this->totalDays > 0
            ? min(100, round(($this->daysElapsed / $this->totalDays) * 100, 1))
            : 0;
        $this->currentWeek = min(12, (int) ceil(max(1, $this->daysElapsed) / 7));
    }

    protected function loadWeeklySummary($client): void
    {
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = now()->endOfWeek(Carbon::SUNDAY);

        // Workout adherence from rise_tracking
        $weekTracking = RiseTracking::where('client_id', $client->id)
            ->whereBetween('log_date', [$startOfWeek, $endOfWeek])
            ->get();

        $this->workoutsThisWeek = $weekTracking->where('training_done', true)->count();
        $this->nutritionDaysThisWeek = $weekTracking->where('nutrition_done', true)->count();

        // Habits from rise_daily_logs
        $program = RiseProgram::where('client_id', $client->id)
            ->where('status', 'active')
            ->first();

        if ($program) {
            $this->habitsCompletedThisWeek = RiseDailyLog::where('rise_program_id', $program->id)
                ->whereBetween('log_date', [$startOfWeek, $endOfWeek])
                ->where('workout_completed', true)
                ->count();
        }
    }

    protected function loadStreak($client): void
    {
        $trackingDays = RiseTracking::where('client_id', $client->id)
            ->where('training_done', true)
            ->orderByDesc('log_date')
            ->pluck('log_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'));

        $streak = 0;
        $checkDate = now()->format('Y-m-d');

        // If no entry today, start checking from yesterday
        if (! $trackingDays->contains($checkDate)) {
            $checkDate = now()->subDay()->format('Y-m-d');
        }

        while ($trackingDays->contains($checkDate)) {
            $streak++;
            $checkDate = Carbon::parse($checkDate)->subDay()->format('Y-m-d');
        }

        $this->currentStreak = $streak;
    }

    protected function loadQuickStats($client): void
    {
        // Latest measurement
        $latest = RiseMeasurement::where('client_id', $client->id)
            ->orderByDesc('log_date')
            ->first();

        $first = RiseMeasurement::where('client_id', $client->id)
            ->orderBy('log_date')
            ->first();

        if ($latest) {
            $this->latestWeight = (float) $latest->weight_kg;
            if ($first && $first->id !== $latest->id) {
                $this->weightChange = round((float) $latest->weight_kg - (float) $first->weight_kg, 1);
            }
        }

        // Total tracking days
        $this->totalTrackingDays = RiseTracking::where('client_id', $client->id)->count();

        // Overall adherence (training done / total days elapsed)
        if ($this->daysElapsed > 0) {
            $trainingDone = RiseTracking::where('client_id', $client->id)
                ->where('training_done', true)
                ->count();
            $this->overallAdherence = round(($trainingDone / $this->daysElapsed) * 100, 0);
        }
    }

    protected function loadWeeklyGrid($client): void
    {
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);

        $weekTracking = RiseTracking::where('client_id', $client->id)
            ->whereBetween('log_date', [$startOfWeek, $startOfWeek->copy()->addDays(6)])
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->log_date)->dayOfWeekIso);

        $dayLabels = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
        $today = now()->dayOfWeekIso;

        $this->weekDays = [];
        for ($i = 1; $i <= 7; $i++) {
            $entry = $weekTracking->get($i);
            $this->weekDays[] = [
                'label' => $dayLabels[$i - 1],
                'trainingDone' => $entry?->training_done ?? false,
                'nutritionDone' => $entry?->nutrition_done ?? false,
                'isToday' => $i === $today,
            ];
        }
    }

    public function render()
    {
        return view('livewire.rise.dashboard');
    }
}
