<?php

namespace App\Livewire\Client;

use App\Models\HabitLog;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Habitos Diarios — WellCore'])]
class HabitTracker extends Component
{
    /** @var array<string, array{label: string, icon: string}> */
    public const HABITS = [
        'agua' => ['label' => 'Agua', 'icon' => 'water'],
        'sueno' => ['label' => 'Sueno', 'icon' => 'moon'],
        'entrenamiento' => ['label' => 'Entrenamiento', 'icon' => 'dumbbell'],
        'nutricion' => ['label' => 'Nutricion', 'icon' => 'apple'],
        'suplementos' => ['label' => 'Suplementos', 'icon' => 'pill'],
    ];

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
        } else {
            HabitLog::create([
                'client_id' => $clientId,
                'log_date' => $today,
                'habit_type' => $habitType,
                'value' => true,
            ]);
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

        $todayHabits = [];
        foreach (self::HABITS as $type => $meta) {
            $todayHabits[$type] = [
                'label' => $meta['label'],
                'icon' => $meta['icon'],
                'completed' => isset($todayLogs[$type]) && $todayLogs[$type]->value,
            ];
        }

        $completedToday = collect($todayHabits)->where('completed', true)->count();
        $totalHabits = count(self::HABITS);

        // Weekly overview (Monday through Sunday of current week)
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

        return view('livewire.client.habit-tracker', [
            'todayHabits' => $todayHabits,
            'completedToday' => $completedToday,
            'totalHabits' => $totalHabits,
            'weeklyData' => $weeklyData,
        ]);
    }
}
