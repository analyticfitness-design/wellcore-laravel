<?php

namespace App\Livewire\Client;

use App\Models\Checkin;
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

    #[Validate('required|in:excelente,buena,regular,mala')]
    public string $nutricion = 'regular';

    #[Validate('required|integer|min:1|max:10')]
    public int $rpe = 5;

    #[Validate('nullable|string|max:1000')]
    public string $comentario = '';

    public bool $showSuccess = false;

    public function setBienestar(int $value): void
    {
        $this->bienestar = $value;
    }

    public function submit(): void
    {
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
        ]);

        $this->reset(['bienestar', 'diasEntrenados', 'nutricion', 'rpe', 'comentario']);
        $this->bienestar = 3;
        $this->diasEntrenados = 0;
        $this->nutricion = 'regular';
        $this->rpe = 5;

        $this->showSuccess = true;
    }

    public function dismissSuccess(): void
    {
        $this->showSuccess = false;
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();

        $recentCheckins = Checkin::where('client_id', $clientId)
            ->orderByDesc('checkin_date')
            ->limit(10)
            ->get();

        return view('livewire.client.checkin-form', [
            'recentCheckins' => $recentCheckins,
        ]);
    }
}
