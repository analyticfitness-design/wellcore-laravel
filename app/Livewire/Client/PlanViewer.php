<?php

namespace App\Livewire\Client;

use App\Models\AssignedPlan;
use App\Models\BloodworkResult;
use App\Models\HabitLog;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class PlanViewer extends Component
{
    public ?array $trainingPlan = null;
    public ?array $nutritionPlan = null;
    public ?array $supplementPlan = null;
    public string $activeTab = 'entrenamiento';
    public string $clientPlanType = 'esencial';

    // Habits
    public array $habitData = [];
    public float $habitCompliance = 0;

    // Bloodwork
    public array $bloodworkResults = [];
    public string $bwTestName = '';
    public string $bwValue = '';
    public string $bwUnit = '';
    public string $bwReferenceRange = '';
    public string $bwTestDate = '';
    public bool $bwShowSuccess = false;

    public function mount(): void
    {
        $user = auth('wellcore')->user();
        $clientId = $user?->id ?? auth('wellcore')->id();
        $plan = $user->plan ?? 'esencial';
        $this->clientPlanType = strtolower($plan instanceof \App\Enums\PlanType ? $plan->value : (string) $plan);

        $plans = AssignedPlan::where('client_id', $clientId)
            ->where('active', true)
            ->get();

        foreach ($plans as $plan) {
            $content = is_array($plan->content)
                ? $plan->content
                : json_decode($plan->content, true);

            match ($plan->plan_type) {
                'entrenamiento' => $this->trainingPlan = $content,
                'nutricion' => $this->nutritionPlan = $content,
                'suplementacion' => $this->supplementPlan = $content,
                default => null,
            };
        }

        $this->loadHabits($clientId);
        $this->loadBloodwork($clientId);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    protected function loadHabits(int $clientId): void
    {
        $startDate = Carbon::now()->subDays(30);
        $today = Carbon::today();

        $logs = HabitLog::where('client_id', $clientId)
            ->where('log_date', '>=', $startDate)
            ->orderBy('log_date', 'desc')
            ->get();

        $habitTypes = ['agua', 'sueno', 'nutricion', 'estres'];
        $habitLabels = [
            'agua' => 'Agua',
            'sueno' => 'Sueno',
            'nutricion' => 'Nutricion',
            'estres' => 'Estres',
        ];
        $habitIcons = [
            'agua' => 'droplet',
            'sueno' => 'moon',
            'nutricion' => 'utensils',
            'estres' => 'brain',
        ];

        $this->habitData = [];

        foreach ($habitTypes as $type) {
            $typeLogs = $logs->where('habit_type', $type);

            // Average value this month
            $avg = $typeLogs->count() > 0
                ? round($typeLogs->avg('value'), 1)
                : 0;

            // Streak: consecutive days with value > 0 going back from today
            $streak = 0;
            $checkDate = $today->copy();
            for ($i = 0; $i < 30; $i++) {
                $dayLog = $typeLogs->first(function ($log) use ($checkDate) {
                    return $log->log_date->format('Y-m-d') === $checkDate->format('Y-m-d');
                });
                if ($dayLog && $dayLog->value > 0) {
                    $streak++;
                    $checkDate->subDay();
                } else {
                    break;
                }
            }

            // Last 7 days values for visual dots
            $last7 = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $dayLog = $typeLogs->first(function ($log) use ($date) {
                    return $log->log_date->format('Y-m-d') === $date->format('Y-m-d');
                });
                $last7[] = [
                    'date' => $date->format('D'),
                    'value' => $dayLog ? $dayLog->value : 0,
                ];
            }

            $this->habitData[] = [
                'type' => $type,
                'label' => $habitLabels[$type],
                'icon' => $habitIcons[$type],
                'streak' => $streak,
                'average' => $avg,
                'last7' => $last7,
            ];
        }

        // Compliance: days with at least 1 habit logged / days in current month so far
        $daysInMonth = $today->day;
        $monthStart = $today->copy()->startOfMonth();
        $daysWithLogs = $logs
            ->where('log_date', '>=', $monthStart)
            ->pluck('log_date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->unique()
            ->count();

        $this->habitCompliance = $daysInMonth > 0
            ? round(($daysWithLogs / $daysInMonth) * 100, 0)
            : 0;
    }

    protected function loadBloodwork(int $clientId): void
    {
        $this->bloodworkResults = BloodworkResult::where('client_id', $clientId)
            ->orderBy('test_date', 'desc')
            ->get()
            ->toArray();
    }

    public function saveBloodwork(): void
    {
        $this->validate([
            'bwTestName' => 'required|string|max:100',
            'bwValue' => 'required|numeric',
            'bwUnit' => 'required|string|max:30',
            'bwReferenceRange' => 'nullable|string|max:50',
            'bwTestDate' => 'required|date',
        ]);

        $clientId = auth('wellcore')->id();

        BloodworkResult::create([
            'client_id' => $clientId,
            'test_name' => $this->bwTestName,
            'value' => $this->bwValue,
            'unit' => $this->bwUnit,
            'reference_range' => $this->bwReferenceRange,
            'test_date' => $this->bwTestDate,
        ]);

        // Reset form
        $this->bwTestName = '';
        $this->bwValue = '';
        $this->bwUnit = '';
        $this->bwReferenceRange = '';
        $this->bwTestDate = '';
        $this->bwShowSuccess = true;

        $this->loadBloodwork($clientId);
    }

    public function deleteBloodwork(int $id): void
    {
        $clientId = auth('wellcore')->id();

        BloodworkResult::where('id', $id)
            ->where('client_id', $clientId)
            ->delete();

        $this->loadBloodwork($clientId);
    }

    public function render()
    {
        return view('livewire.client.plan-viewer');
    }
}
