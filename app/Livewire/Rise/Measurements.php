<?php

namespace App\Livewire\Rise;

use App\Models\RiseMeasurement;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.rise', ['title' => 'Mediciones'])]
class Measurements extends Component
{
    // Form fields
    #[Validate('required|numeric|min:30|max:300')]
    public ?float $weight_kg = null;

    #[Validate('nullable|numeric|min:30|max:200')]
    public ?float $chest_cm = null;

    #[Validate('nullable|numeric|min:30|max:200')]
    public ?float $waist_cm = null;

    #[Validate('nullable|numeric|min:30|max:200')]
    public ?float $hips_cm = null;

    #[Validate('nullable|numeric|min:20|max:100')]
    public ?float $thigh_cm = null;

    #[Validate('nullable|numeric|min:15|max:60')]
    public ?float $arm_cm = null;

    #[Validate('nullable|numeric|min:0|max:100')]
    public ?float $muscle_pct = null;

    #[Validate('nullable|numeric|min:0|max:100')]
    public ?float $fat_pct = null;

    // State
    public bool $showForm = false;
    public bool $saved = false;

    // Comparison data
    public ?array $firstMeasurement = null;
    public ?array $latestMeasurement = null;
    public array $history = [];

    public function mount(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $client = auth('wellcore')->user();

        $measurements = RiseMeasurement::where('client_id', $client->id)
            ->orderByDesc('log_date')
            ->get();

        // History for the table
        $this->history = $measurements->map(fn ($m) => [
            'id' => $m->id,
            'date' => $m->log_date?->format('d M Y'),
            'weight_kg' => $m->weight_kg ? (float) $m->weight_kg : null,
            'chest_cm' => $m->chest_cm ? (float) $m->chest_cm : null,
            'waist_cm' => $m->waist_cm ? (float) $m->waist_cm : null,
            'hips_cm' => $m->hips_cm ? (float) $m->hips_cm : null,
            'thigh_cm' => $m->thigh_cm ? (float) $m->thigh_cm : null,
            'arm_cm' => $m->arm_cm ? (float) $m->arm_cm : null,
            'muscle_pct' => $m->muscle_pct ? (float) $m->muscle_pct : null,
            'fat_pct' => $m->fat_pct ? (float) $m->fat_pct : null,
        ])->toArray();

        // First & Latest for comparison
        if ($measurements->count() > 0) {
            $latest = $measurements->first();
            $this->latestMeasurement = [
                'date' => $latest->log_date?->format('d M Y'),
                'weight_kg' => $latest->weight_kg ? (float) $latest->weight_kg : null,
                'chest_cm' => $latest->chest_cm ? (float) $latest->chest_cm : null,
                'waist_cm' => $latest->waist_cm ? (float) $latest->waist_cm : null,
                'hips_cm' => $latest->hips_cm ? (float) $latest->hips_cm : null,
                'thigh_cm' => $latest->thigh_cm ? (float) $latest->thigh_cm : null,
                'arm_cm' => $latest->arm_cm ? (float) $latest->arm_cm : null,
                'muscle_pct' => $latest->muscle_pct ? (float) $latest->muscle_pct : null,
                'fat_pct' => $latest->fat_pct ? (float) $latest->fat_pct : null,
            ];

            if ($measurements->count() > 1) {
                $first = $measurements->last();
                $this->firstMeasurement = [
                    'date' => $first->log_date?->format('d M Y'),
                    'weight_kg' => $first->weight_kg ? (float) $first->weight_kg : null,
                    'chest_cm' => $first->chest_cm ? (float) $first->chest_cm : null,
                    'waist_cm' => $first->waist_cm ? (float) $first->waist_cm : null,
                    'hips_cm' => $first->hips_cm ? (float) $first->hips_cm : null,
                    'thigh_cm' => $first->thigh_cm ? (float) $first->thigh_cm : null,
                    'arm_cm' => $first->arm_cm ? (float) $first->arm_cm : null,
                    'muscle_pct' => $first->muscle_pct ? (float) $first->muscle_pct : null,
                    'fat_pct' => $first->fat_pct ? (float) $first->fat_pct : null,
                ];
            }
        }
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        $this->saved = false;
    }

    public function save(): void
    {
        $this->validate();

        $client = auth('wellcore')->user();

        RiseMeasurement::create([
            'client_id' => $client->id,
            'log_date' => now()->toDateString(),
            'weight_kg' => $this->weight_kg,
            'chest_cm' => $this->chest_cm,
            'waist_cm' => $this->waist_cm,
            'hips_cm' => $this->hips_cm,
            'thigh_cm' => $this->thigh_cm,
            'arm_cm' => $this->arm_cm,
            'muscle_pct' => $this->muscle_pct,
            'fat_pct' => $this->fat_pct,
        ]);

        // Reset form
        $this->reset(['weight_kg', 'chest_cm', 'waist_cm', 'hips_cm', 'thigh_cm', 'arm_cm', 'muscle_pct', 'fat_pct']);
        $this->saved = true;
        $this->showForm = false;
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.rise.measurements');
    }
}
