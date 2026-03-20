<?php

namespace App\Livewire\Rise;

use App\Models\RiseTracking;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.rise', ['title' => 'Tracking Diario'])]
class DailyTracking extends Component
{
    // Form fields
    #[Validate('boolean')]
    public bool $trainingDone = false;

    #[Validate('boolean')]
    public bool $nutritionDone = false;

    #[Validate('nullable|numeric|min:0|max:10')]
    public ?float $waterLiters = null;

    #[Validate('nullable|numeric|min:0|max:24')]
    public ?float $sleepHours = null;

    #[Validate('nullable|string|max:500')]
    public ?string $note = null;

    // State
    public bool $todaySaved = false;
    public ?string $savedAt = null;

    // Weekly grid
    public array $weekDays = [];

    public function mount(): void
    {
        $client = auth('wellcore')->user();

        // Check if today already has an entry
        $today = RiseTracking::where('client_id', $client->id)
            ->where('log_date', now()->toDateString())
            ->first();

        if ($today) {
            $this->trainingDone = (bool) $today->training_done;
            $this->nutritionDone = (bool) $today->nutrition_done;
            $this->waterLiters = $today->water_liters ? (float) $today->water_liters : null;
            $this->sleepHours = $today->sleep_hours ? (float) $today->sleep_hours : null;
            $this->note = $today->note;
            $this->todaySaved = true;
            $this->savedAt = $today->updated_at?->format('H:i');
        }

        $this->loadWeeklyGrid($client);
    }

    public function save(): void
    {
        $this->validate();

        $client = auth('wellcore')->user();

        RiseTracking::updateOrCreate(
            [
                'client_id' => $client->id,
                'log_date' => now()->toDateString(),
            ],
            [
                'training_done' => $this->trainingDone,
                'nutrition_done' => $this->nutritionDone,
                'water_liters' => $this->waterLiters,
                'sleep_hours' => $this->sleepHours,
                'note' => $this->note,
            ]
        );

        $this->todaySaved = true;
        $this->savedAt = now()->format('H:i');
        $this->loadWeeklyGrid($client);

        $this->dispatch('tracking-saved');
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
                'trainingDone' => (bool) ($entry?->training_done ?? false),
                'nutritionDone' => (bool) ($entry?->nutrition_done ?? false),
                'waterLiters' => $entry?->water_liters ? (float) $entry->water_liters : null,
                'sleepHours' => $entry?->sleep_hours ? (float) $entry->sleep_hours : null,
                'isToday' => $i === $today,
                'hasEntry' => $entry !== null,
            ];
        }
    }

    public function render()
    {
        return view('livewire.rise.daily-tracking');
    }
}
