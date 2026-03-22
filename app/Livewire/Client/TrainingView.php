<?php

namespace App\Livewire\Client;

use App\Models\TrainingLog;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Mi Entrenamiento — WellCore'])]
class TrainingView extends Component
{
    public int $year;

    public int $week;

    public function mount(): void
    {
        $this->year = (int) now()->isoFormat('GGGG');
        $this->week = (int) now()->isoFormat('W');
    }

    public function previousWeek(): void
    {
        $date = Carbon::now()
            ->setISODate($this->year, $this->week)
            ->subWeek();

        $this->year = (int) $date->isoFormat('GGGG');
        $this->week = (int) $date->isoFormat('W');
    }

    public function nextWeek(): void
    {
        // Do not allow navigating beyond the current ISO week
        $currentYear = (int) now()->isoFormat('GGGG');
        $currentWeek = (int) now()->isoFormat('W');

        if ($this->year === $currentYear && $this->week === $currentWeek) {
            return;
        }

        $date = Carbon::now()
            ->setISODate($this->year, $this->week)
            ->addWeek();

        $this->year = (int) $date->isoFormat('GGGG');
        $this->week = (int) $date->isoFormat('W');
    }

    public function goToCurrentWeek(): void
    {
        $this->year = (int) now()->isoFormat('GGGG');
        $this->week = (int) now()->isoFormat('W');
    }

    public function toggleDay(string $date): void
    {
        // Silently block future dates — the blade already disables those buttons
        if ($date > today()->toDateString()) {
            return;
        }

        $clientId = auth('wellcore')->id();

        $log = TrainingLog::where('client_id', $clientId)
            ->where('log_date', $date)
            ->first();

        if ($log) {
            $log->update(['completed' => ! $log->completed]);
        } else {
            $parsed = Carbon::parse($date);
            TrainingLog::create([
                'client_id' => $clientId,
                'log_date' => $date,
                'completed' => true,
                'year_num' => (int) $parsed->isoFormat('GGGG'),
                'week_num' => (int) $parsed->isoFormat('W'),
            ]);
        }
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();

        $logs = TrainingLog::where('client_id', $clientId)
            ->where('year_num', $this->year)
            ->where('week_num', $this->week)
            ->get()
            ->keyBy(fn ($log) => $log->log_date->format('Y-m-d'));

        // Build the 7 days of this ISO week (Monday through Sunday)
        $startOfWeek = Carbon::now()->setISODate($this->year, $this->week, 1); // Monday
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dateKey = $day->format('Y-m-d');
            $days[] = [
                'date' => $dateKey,
                'dayNumber' => $day->format('d'),
                'dayName' => $day->locale('es')->isoFormat('ddd'),
                'isToday' => $day->isToday(),
                'completed' => isset($logs[$dateKey]) && $logs[$dateKey]->completed,
            ];
        }

        $completedCount = collect($days)->where('completed', true)->count();

        // Monthly stats
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $monthSessions = TrainingLog::where('client_id', $clientId)
            ->where('completed', true)
            ->whereBetween('log_date', [$monthStart, $monthEnd])
            ->count();

        $isCurrentWeek = $this->year === (int) now()->isoFormat('GGGG')
            && $this->week === (int) now()->isoFormat('W');

        return view('livewire.client.training-view', [
            'days' => $days,
            'completedCount' => $completedCount,
            'monthSessions' => $monthSessions,
            'isCurrentWeek' => $isCurrentWeek,
        ]);
    }
}
