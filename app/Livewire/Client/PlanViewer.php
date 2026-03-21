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
        $plan = $user?->plan ?? 'esencial';
        $this->clientPlanType = strtolower($plan instanceof \App\Enums\PlanType ? $plan->value : (string) $plan);

        $plans = AssignedPlan::where('client_id', $clientId)
            ->where('active', true)
            ->get();

        foreach ($plans as $plan) {
            $content = is_array($plan->content)
                ? $plan->content
                : json_decode($plan->content, true);

            match ($plan->plan_type) {
                'entrenamiento' => $this->trainingPlan = $this->normalizeTrainingPlan($content),
                'nutricion'     => $this->nutritionPlan = $content,
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

    /**
     * Normalize training plan JSON so English keys (weeks/days/exercises/name/sets/reps)
     * are mapped to the Spanish keys the Blade view expects (dias/ejercicios/nombre/series).
     */
    private function normalizeTrainingPlan(?array $content): ?array
    {
        if (! $content) {
            return null;
        }

        // Top-level: 'weeks' or 'days' → 'dias'
        if (! isset($content['dias'])) {
            $days = $content['weeks'] ?? $content['days'] ?? null;
            if ($days !== null) {
                $content['dias'] = $days;
                unset($content['weeks'], $content['days']);
            }
        }

        if (! isset($content['dias']) || ! is_array($content['dias'])) {
            return $content;
        }

        foreach ($content['dias'] as &$dia) {
            if (! is_array($dia)) {
                continue;
            }

            // Day name: 'name' → 'nombre'
            if (! isset($dia['nombre']) && isset($dia['name'])) {
                $dia['nombre'] = $dia['name'];
            }
            // Day identifier: 'day' → 'dia'
            if (! isset($dia['dia']) && isset($dia['day'])) {
                $dia['dia'] = $dia['day'];
            }

            // Exercises list: 'exercises' or 'sessions' → 'ejercicios'
            if (! isset($dia['ejercicios'])) {
                $exercises = $dia['exercises'] ?? $dia['sessions'] ?? null;
                if ($exercises !== null) {
                    $dia['ejercicios'] = $exercises;
                    unset($dia['exercises'], $dia['sessions']);
                }
            }

            if (isset($dia['ejercicios']) && is_array($dia['ejercicios'])) {
                foreach ($dia['ejercicios'] as &$ej) {
                    if (! is_array($ej)) {
                        continue;
                    }
                    // Exercise name
                    if (! isset($ej['nombre']) && isset($ej['name'])) {
                        $ej['nombre'] = $ej['name'];
                    }
                    if (! isset($ej['ejercicio']) && isset($ej['exercise'])) {
                        $ej['ejercicio'] = $ej['exercise'];
                    }
                    // Sets / reps
                    if (! isset($ej['series']) && isset($ej['sets'])) {
                        $ej['series'] = $ej['sets'];
                    }
                    if (! isset($ej['repeticiones']) && isset($ej['reps'])) {
                        $ej['repeticiones'] = $ej['reps'];
                    }
                }
                unset($ej);
            }
        }
        unset($dia);

        return $content;
    }

    public function render()
    {
        return view('livewire.client.plan-viewer');
    }
}
