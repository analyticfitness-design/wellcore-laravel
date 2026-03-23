<?php

namespace App\Livewire\Client;

use App\Models\HabitLog;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Hábitos Diarios — WellCore'])]
class HabitTracker extends Component
{
    /** @var array<string, array{label: string, icon: string, tip: string}> */
    public const HABITS = [
        'agua' => ['label' => 'Agua', 'icon' => 'water', 'tip' => 'Tu cuerpo necesita al menos 2 litros diarios para un rendimiento óptimo.'],
        'sueno' => ['label' => 'Sueño', 'icon' => 'moon', 'tip' => 'Dormir 7-9 horas mejora tu recuperación muscular y equilibrio hormonal.'],
        'entrenamiento' => ['label' => 'Entrenamiento', 'icon' => 'dumbbell', 'tip' => 'La consistencia supera la intensidad. Cada sesión cuenta.'],
        'nutricion' => ['label' => 'Nutrición', 'icon' => 'apple', 'tip' => 'Cumplir tu plan de nutrición es lo que realmente transforma tu cuerpo.'],
        'suplementos' => ['label' => 'Suplementos', 'icon' => 'pill', 'tip' => 'Toma tus suplementos a la misma hora cada día para máxima absorción.'],
    ];

    public bool $showConfetti = false;

    /** Show habits onboarding tutorial for first-time users */
    public bool $showTutorial = false;

    public function mount(): void
    {
        $clientId = auth('wellcore')->id();

        // Show tutorial if client has never logged a habit
        $this->showTutorial = !HabitLog::where('client_id', $clientId)->exists();
    }

    public function dismissTutorial(): void
    {
        $this->showTutorial = false;
    }

    public function toggleHabit(string $habitType): void
    {
        if (! array_key_exists($habitType, self::HABITS)) {
            return;
        }

        // Water is managed exclusively by NutritionPlan::toggleWater()
        // to preserve the ml-accumulation contract (value = integer ml, not bool).
        if ($habitType === 'agua') {
            return;
        }

        $clientId = auth('wellcore')->id();
        $today = now()->toDateString();

        $log = HabitLog::where('client_id', $clientId)
            ->where('log_date', $today)
            ->where('habit_type', $habitType)
            ->first();

        if ($log) {
            $log->update(['value' => ! $log->value]);
            if (! $log->value) {
                $this->showConfetti = false;
            }
        } else {
            HabitLog::create([
                'client_id' => $clientId,
                'log_date' => $today,
                'habit_type' => $habitType,
                'value' => true,
            ]);
        }

        // Check if all habits completed.
        // Water uses value >= 1 (ml); all others use value == 1 (bool cast to int).
        $completedCount = HabitLog::where('client_id', $clientId)
            ->where('log_date', $today)
            ->where('value', '>=', 1)
            ->count();

        if ($completedCount >= count(self::HABITS)) {
            $this->showConfetti = true;
        }
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();
        $today = now()->toDateString();

        // Today's habits
        $todayLogs = HabitLog::where('client_id', $clientId)
            ->where('log_date', $today)
            ->get()
            ->keyBy('habit_type');

        // Last 90 days logs for streaks (streak loop iterates up to 90 days).
        // Fetch all logs without a value filter so water logs (value = ml int) are included.
        $last90Logs = HabitLog::where('client_id', $clientId)
            ->where('log_date', '>=', now()->subDays(90)->toDateString())
            ->where('value', '>=', 1)
            ->get();

        $todayHabits = [];
        foreach (self::HABITS as $type => $meta) {
            // Streak calculation — runs up to 90 days, backed by 90-day data.
            // If today is not yet logged, start counting from yesterday so that
            // an unlogged today does not break an otherwise intact streak.
            $todayLogged = $last90Logs->contains(fn ($l) =>
                $l->habit_type === $type &&
                $l->log_date->format('Y-m-d') === $today
            );
            $streak = 0;
            $checkDate = $todayLogged ? now()->copy() : now()->subDay();
            for ($i = 0; $i < 90; $i++) {
                $hasLog = $last90Logs->contains(fn ($l) =>
                    $l->habit_type === $type &&
                    $l->log_date->format('Y-m-d') === $checkDate->format('Y-m-d')
                );
                if ($hasLog) {
                    $streak++;
                    $checkDate->subDay();
                } else {
                    break;
                }
            }

            // 30-day compliance (uses the 90-day collection, filtered to last 30 days).
            $thirtyDaysAgo = now()->subDays(30)->toDateString();
            $daysCompleted = $last90Logs
                ->where('habit_type', $type)
                ->filter(fn ($l) => $l->log_date->toDateString() >= $thirtyDaysAgo)
                ->count();
            $compliance = min(100, round(($daysCompleted / 30) * 100));

            // "completed" check: water uses value >= 1 (ml); others use value == 1.
            $logEntry = $todayLogs[$type] ?? null;
            $completed = $logEntry !== null && (int) $logEntry->value >= 1;

            $todayHabits[$type] = [
                'label' => $meta['label'],
                'icon' => $meta['icon'],
                'tip' => $meta['tip'],
                'completed' => $completed,
                'streak' => $streak,
                'compliance' => $compliance,
            ];
        }

        $completedToday = collect($todayHabits)->where('completed', true)->count();
        $totalHabits = count(self::HABITS);

        // Weekly overview
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weeklyData = [];
        $weeklyPossibleDays = 0;
        $weeklyCompletedHabits = 0;

        $weekLogs = HabitLog::where('client_id', $clientId)
            ->whereBetween('log_date', [
                $startOfWeek->toDateString(),
                $startOfWeek->copy()->addDays(6)->toDateString(),
            ])
            ->get()
            ->groupBy(fn ($log) => $log->log_date->format('Y-m-d'));

        $todayDate = Carbon::today();

        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dateKey = $day->format('Y-m-d');
            $isFutureDay = $day->gt($todayDate);
            $dayLogs = $weekLogs->get($dateKey, collect());
            $dayCompleted = $dayLogs->filter(fn ($l) => (int) $l->value >= 1)->count();

            // Only past days and today count toward the weekly compliance denominator
            if (! $isFutureDay) {
                $weeklyPossibleDays++;
                $weeklyCompletedHabits += $dayCompleted;
            }

            $weeklyData[] = [
                'date' => $dateKey,
                'dayName' => $day->locale('es')->isoFormat('dd'),
                'dayNumber' => $day->format('d'),
                'isToday' => $day->isToday(),
                'isFuture' => $isFutureDay,
                'completed' => $dayCompleted,
                'total' => $totalHabits,
            ];
        }

        // Weekly compliance: completed habits over possible habit-days (excludes future days).
        // Max possible = $weeklyPossibleDays * $totalHabits.
        $weeklyComplianceMax = $weeklyPossibleDays * $totalHabits;
        $weeklyCompliance = $weeklyComplianceMax > 0
            ? min(100, (int) round(($weeklyCompletedHabits / $weeklyComplianceMax) * 100))
            : 0;

        // Monthly heatmap (last 30 days) — single query with GROUP BY
        $heatmapCounts = HabitLog::where('client_id', $clientId)
            ->where('log_date', '>=', now()->subDays(29)->toDateString())
            ->where('value', '>=', 1)
            ->selectRaw('DATE(log_date) as date_key, COUNT(*) as cnt')
            ->groupBy('date_key')
            ->pluck('cnt', 'date_key');

        $heatmapData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $dayLogs = $heatmapCounts->get($dateKey, 0);

            $heatmapData[] = [
                'date' => $dateKey,
                'day' => $date->format('d'),
                'count' => $dayLogs,
                'total' => $totalHabits,
                'level' => $totalHabits > 0 ? min(4, intdiv($dayLogs * 4, $totalHabits)) : 0,
            ];
        }

        return view('livewire.client.habit-tracker', [
            'todayHabits' => $todayHabits,
            'completedToday' => $completedToday,
            'totalHabits' => $totalHabits,
            'weeklyData' => $weeklyData,
            'weeklyCompliance' => $weeklyCompliance,
            'weeklyPossibleDays' => $weeklyPossibleDays,
            'heatmapData' => $heatmapData,
        ]);
    }
}
