<?php

namespace App\Livewire\Public;

use App\Models\Inscription;
use Illuminate\Support\Str;
use Livewire\Component;

class PresencialForm extends Component
{
    public string $nombre = '';
    public string $apellido = '';
    public string $email = '';
    public string $whatsapp = '';
    public ?int $edad = null;
    public string $ciudad = 'Bogota';
    public string $objetivo = '';
    public string $experiencia = '';
    public string $horario = '';
    public string $dias_disponibles = '';
    public string $lesion = '';
    public string $detalle_lesion = '';
    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:50',
            'edad' => 'required|integer|min:16|max:80',
            'objetivo' => 'required|string|max:500',
            'experiencia' => 'required|in:principiante,intermedio,avanzado',
            'horario' => 'required|string|max:100',
            'dias_disponibles' => 'required|in:3,4,5',
            'lesion' => 'required|in:si,no',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        Inscription::create([
            'id' => Str::ulid(),
            'plan' => 'esencial', // presencial uses 'esencial' enum value as closest match
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            'edad' => $this->edad,
            'ciudad' => $this->ciudad,
            'pais' => 'Colombia',
            'objetivo' => $this->objetivo,
            'experiencia' => $this->experiencia,
            'horario' => $this->horario,
            'dias_disponibles' => $this->dias_disponibles,
            'lesion' => $this->lesion,
            'detalle_lesion' => $this->detalle_lesion,
            'como_conocio' => 'presencial',
            'status' => 'pending_contact',
            'ip_hash' => hash('sha256', request()->ip()),
        ]);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.public.presencial-form')
            ->layout('components.layouts.public');
    }
}
