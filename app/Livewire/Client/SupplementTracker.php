<?php

namespace App\Livewire\Client;

use App\Models\AssignedPlan;
use App\Models\SupplementLog;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Suplementación — WellCore'])]
class SupplementTracker extends Component
{
    public ?array $supplementPlan = null;
    public string $selectedDate = '';
    public bool $showHistory = false;

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
        $this->loadPlan();
    }

    protected function loadPlan(): void
    {
        $plan = AssignedPlan::where('client_id', auth('wellcore')->id())
            ->where('plan_type', 'suplementacion')
            ->where('active', true)
            ->latest()
            ->first();

        if ($plan && $plan->content) {
            $content = is_array($plan->content)
                ? $plan->content
                : json_decode($plan->content, true);

            $this->supplementPlan = $content;
        }
    }

    public function toggleSupplement(string $supplementName, string $timing): void
    {
        $clientId = auth('wellcore')->id();

        $log = SupplementLog::where('client_id', $clientId)
            ->where('log_date', $this->selectedDate)
            ->where('supplement_name', $supplementName)
            ->where('timing', $timing)
            ->first();

        if ($log) {
            $log->update(['taken' => !$log->taken]);
        } else {
            SupplementLog::create([
                'client_id' => $clientId,
                'log_date' => $this->selectedDate,
                'supplement_name' => $supplementName,
                'timing' => $timing,
                'taken' => true,
            ]);
        }
    }

    public function goToDate(string $direction): void
    {
        $date = Carbon::parse($this->selectedDate);
        $this->selectedDate = $direction === 'next'
            ? $date->addDay()->toDateString()
            : $date->subDay()->toDateString();
    }

    public function goToToday(): void
    {
        $this->selectedDate = now()->toDateString();
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();

        // Build supplements array with timing and status
        $supplements = [];
        $timingLabels = [
            'manana' => 'Mañana',
            'tarde' => 'Tarde',
            'noche' => 'Noche',
            'pre' => 'Pre-entreno',
            'post' => 'Post-entreno',
        ];

        if ($this->supplementPlan && isset($this->supplementPlan['suplementos'])) {
            $todayLogs = SupplementLog::where('client_id', $clientId)
                ->where('log_date', $this->selectedDate)
                ->get()
                ->groupBy(fn ($l) => $l->supplement_name . '|' . $l->timing);

            foreach ($this->supplementPlan['suplementos'] as $supp) {
                $name = $supp['nombre'] ?? $supp['name'] ?? '';
                $dose = $supp['dosis'] ?? $supp['dose'] ?? '';
                $timings = $supp['horarios'] ?? $supp['timing'] ?? ['manana'];
                $notes = $supp['notas'] ?? $supp['notes'] ?? '';

                if (!is_array($timings)) {
                    $timings = [$timings];
                }

                $timingStatus = [];
                foreach ($timings as $t) {
                    $key = $name . '|' . $t;
                    $log = $todayLogs->get($key)?->first();
                    $timingStatus[] = [
                        'timing' => $t,
                        'label' => $timingLabels[$t] ?? ucfirst($t),
                        'taken' => $log && $log->taken,
                    ];
                }

                $allTaken = collect($timingStatus)->every(fn ($ts) => $ts['taken']);
                $anyTaken = collect($timingStatus)->contains(fn ($ts) => $ts['taken']);

                $supplements[] = [
                    'name' => $name,
                    'dose' => $dose,
                    'notes' => $notes,
                    'timings' => $timingStatus,
                    'allTaken' => $allTaken,
                    'anyTaken' => $anyTaken,
                ];
            }
        }

        // Weekly adherence (last 7 days)
        $weekStart = Carbon::parse($this->selectedDate)->subDays(6);
        $weekLogs = SupplementLog::where('client_id', $clientId)
            ->whereBetween('log_date', [$weekStart->toDateString(), $this->selectedDate])
            ->where('taken', true)
            ->get();

        $totalExpected = 0;
        $totalTaken = 0;
        if ($this->supplementPlan && isset($this->supplementPlan['suplementos'])) {
            foreach ($this->supplementPlan['suplementos'] as $supp) {
                $timings = $supp['horarios'] ?? $supp['timing'] ?? ['manana'];
                if (!is_array($timings)) $timings = [$timings];
                $totalExpected += count($timings) * 7;
            }
            $totalTaken = $weekLogs->count();
        }
        $weeklyAdherence = $totalExpected > 0 ? min(100, round(($totalTaken / $totalExpected) * 100)) : 0;

        // Daily adherence for sparkline
        $dailyAdherence = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::parse($this->selectedDate)->subDays($i);
            $dayLogs = $weekLogs->where('log_date', $date->toDateString());
            $expectedPerDay = 0;
            if ($this->supplementPlan && isset($this->supplementPlan['suplementos'])) {
                foreach ($this->supplementPlan['suplementos'] as $supp) {
                    $timings = $supp['horarios'] ?? $supp['timing'] ?? ['manana'];
                    if (!is_array($timings)) $timings = [$timings];
                    $expectedPerDay += count($timings);
                }
            }
            $dailyAdherence[] = [
                'day' => $date->locale('es')->isoFormat('dd'),
                'date' => $date->format('d'),
                'taken' => $dayLogs->count(),
                'expected' => $expectedPerDay,
                'pct' => $expectedPerDay > 0 ? min(100, round(($dayLogs->count() / $expectedPerDay) * 100)) : 0,
                'isSelected' => $date->format('Y-m-d') === $this->selectedDate,
            ];
        }

        $isToday = $this->selectedDate === now()->toDateString();
        $totalToday = count($supplements);
        $completedToday = collect($supplements)->where('allTaken', true)->count();

        return view('livewire.client.supplement-tracker', [
            'supplements' => $supplements,
            'weeklyAdherence' => $weeklyAdherence,
            'dailyAdherence' => $dailyAdherence,
            'isToday' => $isToday,
            'totalToday' => $totalToday,
            'completedToday' => $completedToday,
        ]);
    }
}
