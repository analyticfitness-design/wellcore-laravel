<?php

namespace App\Livewire\Rise;

use App\Models\RiseProgram;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rise', ['title' => 'Mi Programa'])]
class ProgramView extends Component
{
    // Program info
    public bool $hasProgram = false;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $experienceLevel = null;
    public ?string $trainingLocation = null;
    public ?string $gender = null;
    public ?string $status = null;
    public int $currentWeek = 1;
    public float $progressPct = 0;

    // Personalized program content
    public ?array $program = null;

    public function mount(): void
    {
        $client = auth('wellcore')->user();

        $riseProgram = RiseProgram::where('client_id', $client->id)
            ->where('status', 'active')
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
        $this->program = $riseProgram->personalized_program;

        $totalDays = $riseProgram->start_date && $riseProgram->end_date
            ? Carbon::parse($riseProgram->start_date)->diffInDays($riseProgram->end_date)
            : 84;
        $daysElapsed = $riseProgram->start_date
            ? max(0, Carbon::parse($riseProgram->start_date)->diffInDays(now()))
            : 0;
        $this->currentWeek = min(12, (int) ceil(max(1, $daysElapsed) / 7));
        $this->progressPct = $totalDays > 0
            ? min(100, round(($daysElapsed / $totalDays) * 100, 1))
            : 0;
    }

    public function render()
    {
        return view('livewire.rise.program-view');
    }
}
