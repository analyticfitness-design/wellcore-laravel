<?php

namespace App\Livewire\Rise;

use App\Models\RiseHabitsLog;
use App\Models\RiseMeasurement;
use App\Models\RiseProgram;
use App\Models\RiseTracking;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rise', ['title' => 'Mi Perfil RISE'])]
class RiseProfile extends Component
{
    // Client info
    public string $name = '';
    public string $email = '';
    public string $initial = '';

    // Program info
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $experienceLevel = null;
    public ?string $trainingLocation = null;
    public ?string $gender = null;
    public ?string $status = null;

    // Progress
    public int $progressPercent = 0;
    public int $daysInProgram = 0;
    public int $totalDays = 0;

    // Stats
    public int $measurementCount = 0;
    public int $checkinsCount = 0;
    public int $habitsLogged = 0;
    public float $adherence = 0;

    public function mount(): void
    {
        $client = auth('wellcore')->user();

        $this->name = $client->name ?? 'Usuario';
        $this->email = $client->email ?? '';
        $this->initial = strtoupper(substr($this->name, 0, 1));

        $riseProgram = RiseProgram::where('client_id', $client->id)
            ->latest('id')
            ->first();

        if ($riseProgram) {
            $this->startDate = $riseProgram->start_date?->translatedFormat('d M Y');
            $this->endDate = $riseProgram->end_date?->translatedFormat('d M Y');
            $this->experienceLevel = $riseProgram->experience_level;
            $this->trainingLocation = $riseProgram->training_location;
            $this->gender = $riseProgram->gender;
            $this->status = $riseProgram->status;

            // Progress calculation
            if ($riseProgram->start_date && $riseProgram->end_date) {
                $start = Carbon::parse($riseProgram->start_date);
                $end = Carbon::parse($riseProgram->end_date);
                $now = now();

                $this->totalDays = (int) $start->diffInDays($end);
                $this->daysInProgram = (int) min($start->diffInDays($now), $this->totalDays);

                $this->progressPercent = $this->totalDays > 0
                    ? (int) min(round(($this->daysInProgram / $this->totalDays) * 100), 100)
                    : 0;
            }

            // Stats
            $this->measurementCount = RiseMeasurement::where('client_id', $client->id)->count();

            $this->checkinsCount = RiseTracking::where('client_id', $client->id)->count();

            $this->habitsLogged = RiseHabitsLog::where('rise_program_id', $riseProgram->id)
                ->where('client_id', $client->id)
                ->count();

            // Adherence = days with tracking / days in program
            $this->adherence = $this->daysInProgram > 0
                ? round(($this->checkinsCount / $this->daysInProgram) * 100, 1)
                : 0;
        }
    }

    public function render()
    {
        return view('livewire.rise.rise-profile');
    }
}
