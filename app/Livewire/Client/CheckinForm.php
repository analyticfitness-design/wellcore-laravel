<?php

namespace App\Livewire\Client;

use App\Models\Checkin;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Check-in Semanal — WellCore'])]
class CheckinForm extends Component
{
    #[Validate('required|integer|min:1|max:5')]
    public int $bienestar = 3;

    #[Validate('required|integer|min:0|max:7')]
    public int $diasEntrenados = 0;

    #[Validate('required|in:Si,No,Parcial')]
    public string $nutricion = 'Si';

    #[Validate('required|integer|min:1|max:10')]
    public int $rpe = 5;

    #[Validate('nullable|string|max:1000')]
    public string $comentario = '';

    public bool $showSuccess = false;

    /** Saved values displayed in the success overlay after form reset */
    public int $lastDiasEntrenados = 0;
    public int $lastBienestar = 3;

    /** Show check-in onboarding tutorial for first-time users */
    public bool $showTutorial = false;

    /** Whether today is a valid check-in day (Friday or Saturday, Bogotá time) */
    public bool $isCheckinAvailable = true;

    public function mount(): void
    {
        $clientId = auth('wellcore')->id();
        // Show tutorial if client has never submitted a check-in
        $this->showTutorial = !Checkin::where('client_id', $clientId)->exists();

        // Check-in only allowed on Fridays (5) and Saturdays (6) in Bogotá timezone
        $dayOfWeek = now()->timezone('America/Bogota')->dayOfWeek;
        $this->isCheckinAvailable = in_array($dayOfWeek, [\Carbon\Carbon::FRIDAY, \Carbon\Carbon::SATURDAY]);
    }

    public function dismissTutorial(): void
    {
        $this->showTutorial = false;
    }

    public function setBienestar(int $value): void
    {
        $this->bienestar = $value;
    }

    public function submit(): void
    {
        if (!$this->isCheckinAvailable) {
            $this->addError('submit', 'El check-in semanal solo está disponible los viernes y sábados.');
            return;
        }

        $this->validate();

        $clientId  = auth('wellcore')->id();
        $weekLabel = now()->isoFormat('GGGG') . '-W' . str_pad(now()->isoFormat('W'), 2, '0', STR_PAD_LEFT);

        $alreadySubmitted = Checkin::where('client_id', $clientId)
            ->where('week_label', $weekLabel)
            ->exists();

        if ($alreadySubmitted) {
            $this->addError('submit', 'Ya enviaste tu check-in esta semana. El próximo estará disponible el lunes.');
            return;
        }

        Checkin::create([
            'client_id' => $clientId,
            'week_label' => $weekLabel,
            'checkin_date' => now()->toDateString(),
            'bienestar' => $this->bienestar,
            'dias_entrenados' => $this->diasEntrenados,
            'nutricion' => $this->nutricion,
            'comentario' => $this->comentario,
            'rpe' => $this->rpe,
            'created_at' => now(),
        ]);

        // Capture values for the overlay before resetting the form
        $this->lastDiasEntrenados = $this->diasEntrenados;
        $this->lastBienestar = $this->bienestar;

        $this->reset(['bienestar', 'diasEntrenados', 'nutricion', 'rpe', 'comentario']);
        $this->bienestar = 3;
        $this->diasEntrenados = 0;
        $this->nutricion = 'Si';
        $this->rpe = 5;

        $this->showSuccess = true;

        // Bust the cached checkin history so the new entry appears immediately.
        Cache::forget("checkin:recent:{$clientId}");
    }

    public function dismissSuccess(): void
    {
        $this->showSuccess = false;
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();

        // Cache recent check-ins for 5 minutes. The list only changes when
        // submit() creates a new record (which busts this key). This prevents
        // a DB hit on every bienestar rating click or RPE slider adjustment.
        // Store as a plain array to avoid PHP-serialization issues with stale
        // Eloquent models in the cache after schema migrations.
        $cached = Cache::remember("checkin:recent:{$clientId}", 300, function () use ($clientId) {
            return Checkin::where('client_id', $clientId)
                ->orderByDesc('checkin_date')
                ->limit(10)
                ->get()
                ->toArray();
        });
        $recentCheckins = Checkin::hydrate($cached);

        return view('livewire.client.checkin-form', [
            'recentCheckins' => $recentCheckins,
        ]);
    }
}
