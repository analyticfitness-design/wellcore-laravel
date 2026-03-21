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

    public function toggleHabit(string $habitType): void
    {
        if (! array_key_exists($habitType, self::HABITS)) {
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

        // Check if all habits completed
        $completedCount = HabitLog::where('client_id', $clientId)
            ->where('log_date', $today)
            ->where('value', true)
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

        // Last 30 days logs for streaks and rings
        $last30Logs = HabitLog::where('client_id', $clientId)
            ->where('log_date', '>=', now()->subDays(30)->toDateString())
            ->where('value', true)
            ->get();

        $todayHabits = [];
        foreach (self::HABITS as $type => $meta) {
            // Streak calculation
            $streak = 0;
            $checkDate = now()->copy();
            for ($i = 0; $i < 90; $i++) {
                $hasLog = $last30Logs->contains(fn ($l) =>
                    $l->habit_type === $type &&
                    $l->log_date->format('Y-m-d') === $checkDate->format('Y-m-d')
                );
                if ($hasLog || ($i === 0 && ! Carbon::parse($today)->isPast())) {
                    if ($hasLog) $streak++;
                    $checkDate->subDay();
                    if (! $hasLog) break;
                } else {
                    break;
                }
            }

            // 30-day compliance
            $daysCompleted = $last30Logs->where('habit_type', $type)->count();
            $compliance = min(100, round(($daysCompleted / 30) * 100));

            $todayHabits[$type] = [
                'label' => $meta['label'],
                'icon' => $meta['icon'],
                'tip' => $meta['tip'],
                'completed' => isset($todayLogs[$type]) && $todayLogs[$type]->value,
                'streak' => $streak,
                'compliance' => $compliance,
            ];
        }

        $completedToday = collect($todayHabits)->where('completed', true)->count();
        $totalHabits = count(self::HABITS);

        // Weekly overview
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weeklyData = [];

        $weekLogs = HabitLog::where('client_id', $clientId)
            ->whereBetween('log_date', [
                $startOfWeek->toDateString(),
                $startOfWeek->copy()->addDays(6)->toDateString(),
            ])
            ->get()
            ->groupBy(fn ($log) => $log->log_date->format('Y-m-d'));

        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dateKey = $day->format('Y-m-d');
            $dayLogs = $weekLogs->get($dateKey, collect());
            $dayCompleted = $dayLogs->where('value', true)->count();

            $weeklyData[] = [
                'date' => $dateKey,
                'dayName' => $day->locale('es')->isoFormat('dd'),
                'dayNumber' => $day->format('d'),
                'isToday' => $day->isToday(),
                'completed' => $dayCompleted,
                'total' => $totalHabits,
            ];
        }

        // Monthly heatmap (last 30 days)
        $heatmapData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $dayLogs = HabitLog::where('client_id', $clientId)
                ->where('log_date', $dateKey)
                ->where('value', true)
                ->count();

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
            'heatmapData' => $heatmapData,
        ]);
    }
}
