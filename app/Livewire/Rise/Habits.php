<?php

namespace App\Livewire\Rise;

use App\Models\RiseHabitsLog;
use App\Models\RiseProgram;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.rise', ['title' => 'Habitos RISE'])]
class Habits extends Component
{
    // Universal metrics
    #[Validate('nullable|numeric|min:0|max:10')]
    public ?float $water = null;

    #[Validate('nullable|numeric|min:0|max:24')]
    public ?float $sleep = null;

    #[Validate('nullable|integer|min:0|max:100000')]
    public ?int $steps = null;

    #[Validate('nullable|string|max:500')]
    public ?string $notes = null;

    // Program-driven habits: keyed by string index → bool
    public array $habitsDone = [];

    // Habits plan from the client's personalized_program['plan_habitos']
    public array $habitsPlan = [];

    // State
    public bool $todaySaved = false;
    public ?string $savedAt = null;
    public ?int $riseProgramId = null;

    // Weekly grid
    public array $weekDays = [];

    // Stats
    public int $currentStreak = 0;
    public int $completedDays = 0;
    public ?float $avgWater = null;
    public ?float $avgSleep = null;

    public function mount(): void
    {
        $client = auth('wellcore')->user();

        $riseProgram = RiseProgram::where('client_id', $client->id)
            ->whereIn('status', ['active', 'activo'])
            ->latest('id')
            ->first();

        $this->riseProgramId = $riseProgram?->id;

        // Load habits plan from the personalized program JSON
        $programJson = $riseProgram?->personalized_program ?? [];
        $this->habitsPlan = $programJson['plan_habitos'] ?? [];

        // Load today's log entry
        if ($this->riseProgramId) {
            $today = RiseHabitsLog::where('rise_program_id', $this->riseProgramId)
                ->where('client_id', $client->id)
                ->where('log_date', now()->toDateString())
                ->first();

            if ($today) {
                $this->water = $today->water_liters ? (float) $today->water_liters : null;
                $this->sleep = $today->sleep_hours ? (float) $today->sleep_hours : null;
                $this->steps = $today->steps;
                $this->notes = $today->notes;
                $this->todaySaved = true;
                $this->savedAt = $today->updated_at?->format('H:i');

                // Load dynamic habits_json; fall back to legacy boolean columns
                if ($today->habits_json !== null) {
                    $this->habitsDone = $today->habits_json;
                } else {
                    // Backwards compatibility: map old booleans into indexed slots
                    $this->habitsDone = [
                        '0' => (bool) $today->training_completed,
                        '1' => (bool) $today->nutrition_followed,
                        '2' => (bool) $today->meditation,
                    ];
                }
            }
        }

        $this->loadWeeklyGrid($client);
        $this->loadStats($client);
    }

    public function save(): void
    {
        $this->validate();

        $client = auth('wellcore')->user();

        if (! $this->riseProgramId) {
            $riseProgram = RiseProgram::where('client_id', $client->id)
                ->latest('id')
                ->first();
            $this->riseProgramId = $riseProgram?->id ?? 0;
        }

        RiseHabitsLog::updateOrCreate(
            [
                'rise_program_id' => $this->riseProgramId,
                'client_id' => $client->id,
                'log_date' => now()->toDateString(),
            ],
            [
                'water_liters' => $this->water,
                'sleep_hours' => $this->sleep,
                'steps' => $this->steps,
                'notes' => $this->notes,
                'habits_json' => $this->habitsDone,
                // Maintain backwards compatibility with legacy boolean columns
                'training_completed' => (bool) ($this->habitsDone['0'] ?? false),
                'nutrition_followed' => (bool) ($this->habitsDone['1'] ?? false),
                'meditation' => (bool) ($this->habitsDone['2'] ?? false),
            ]
        );

        $this->todaySaved = true;
        $this->savedAt = now()->format('H:i');
        $this->loadWeeklyGrid($client);
        $this->loadStats($client);

        $this->dispatch('habits-saved');
    }

    protected function loadWeeklyGrid($client): void
    {
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);

        $logs = $this->riseProgramId
            ? RiseHabitsLog::where('rise_program_id', $this->riseProgramId)
                ->where('client_id', $client->id)
                ->whereBetween('log_date', [
                    $startOfWeek->toDateString(),
                    $startOfWeek->copy()->addDays(6)->toDateString(),
                ])
                ->get()
                ->keyBy(fn ($item) => Carbon::parse($item->log_date)->dayOfWeekIso)
            : collect();

        $dayLabels = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
        $today = now()->dayOfWeekIso;
        $totalHabits = count($this->habitsPlan) + 3; // program habits + water + sleep + steps

        $this->weekDays = [];
        for ($i = 1; $i <= 7; $i++) {
            $entry = $logs->get($i);
            $habitCount = 0;

            if ($entry) {
                // Count dynamic program habits completed
                $habitsJson = $entry->habits_json ?? [];
                foreach ($habitsJson as $done) {
                    if ($done) {
                        $habitCount++;
                    }
                }

                // Count universal metrics as achieved
                if ($entry->water_liters >= 2) {
                    $habitCount++;
                }
                if ($entry->sleep_hours >= 7) {
                    $habitCount++;
                }
                if ($entry->steps >= 5000) {
                    $habitCount++;
                }
            }

            $this->weekDays[] = [
                'label' => $dayLabels[$i - 1],
                'isToday' => $i === $today,
                'hasEntry' => $entry !== null,
                'habitCount' => $habitCount,
                'total' => $totalHabits > 0 ? $totalHabits : 6,
                'habitsJson' => $entry?->habits_json ?? [],
                'water' => $entry?->water_liters ? (float) $entry->water_liters : null,
                'sleep' => $entry?->sleep_hours ? (float) $entry->sleep_hours : null,
                'steps' => $entry?->steps,
            ];
        }
    }

    protected function loadStats($client): void
    {
        if (! $this->riseProgramId) {
            return;
        }

        $allLogs = RiseHabitsLog::where('rise_program_id', $this->riseProgramId)
            ->where('client_id', $client->id)
            ->orderBy('log_date', 'desc')
            ->get();

        $this->completedDays = $allLogs->count();

        // Calculate streak
        $this->currentStreak = 0;
        $checkDate = now()->toDateString();
        foreach ($allLogs as $log) {
            if ($log->log_date->toDateString() === $checkDate) {
                $this->currentStreak++;
                $checkDate = Carbon::parse($checkDate)->subDay()->toDateString();
            } else {
                break;
            }
        }

        // Averages
        $withWater = $allLogs->whereNotNull('water_liters')->where('water_liters', '>', 0);
        $this->avgWater = $withWater->count() > 0 ? round($withWater->avg('water_liters'), 1) : null;

        $withSleep = $allLogs->whereNotNull('sleep_hours')->where('sleep_hours', '>', 0);
        $this->avgSleep = $withSleep->count() > 0 ? round($withSleep->avg('sleep_hours'), 1) : null;
    }

    public function render()
    {
        return view('livewire.rise.habits');
    }
}
