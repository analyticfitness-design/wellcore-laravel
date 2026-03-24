<?php

namespace App\Livewire\Rise;

use App\Models\RiseProgram;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rise', ['title' => 'Mi Programa'])]
class ProgramView extends Component
{
    // Program meta
    public bool $hasProgram = false;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $experienceLevel = null;
    public ?string $trainingLocation = null;
    public ?string $gender = null;
    public ?string $status = null;
    public int $currentWeek = 1;
    public int $totalWeeks = 12;
    public float $progressPct = 0;

    // Separated program sections (new JSON format)
    public ?array $trainingPlan = null;
    public ?array $nutritionPlan = null;
    public array $habitsPlan = [];

    // Active tab: training | nutrition | habits
    public string $activeTab = 'training';

    public function mount(): void
    {
        $client = auth('wellcore')->user();

        $riseProgram = RiseProgram::where('client_id', $client->id)
            ->whereIn('status', ['active', 'activo'])
            ->first();

        if (! $riseProgram) {
            return;
        }

        $this->hasProgram = true;
        $this->startDate = $riseProgram->start_date?->format('d M Y');
        $this->endDate = $riseProgram->end_date?->format('d M Y');
        $this->experienceLevel = $riseProgram->experience_level;
        $this->trainingLocation = $riseProgram->training_location;
        $this->gender = $riseProgram->gender;
        $this->status = $riseProgram->status;

        $program = $riseProgram->personalized_program ?? [];

        // Extract sections from new JSON format
        $this->trainingPlan = $program['plan_entrenamiento'] ?? null;
        $this->nutritionPlan = $program['plan_nutricion'] ?? null;
        $this->habitsPlan = $program['plan_habitos'] ?? [];

        // Calculate week and progress
        $totalDays = $riseProgram->start_date && $riseProgram->end_date
            ? Carbon::parse($riseProgram->start_date)->diffInDays($riseProgram->end_date)
            : 84;

        $daysElapsed = $riseProgram->start_date
            ? max(0, Carbon::parse($riseProgram->start_date)->diffInDays(now()))
            : 0;

        $this->totalWeeks = $this->trainingPlan['duracion_semanas'] ?? 12;
        $this->currentWeek = min($this->totalWeeks, (int) ceil(max(1, $daysElapsed) / 7));
        $this->progressPct = $totalDays > 0
            ? min(100, round(($daysElapsed / $totalDays) * 100, 1))
            : 0;
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['training', 'nutrition', 'habits'])) {
            $this->activeTab = $tab;
        }
    }

    public function render()
    {
        return view('livewire.rise.program-view');
    }
}
