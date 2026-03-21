<?php

namespace App\Livewire\Public;

use App\Models\Client;
use App\Models\Payment;
use App\Models\RiseProgram;
use Livewire\Component;

class RiseEnrollment extends Component
{
    public int $step = 1;
    public bool $submitted = false;

    // Step 1: Personal data
    public string $nombre = '';
    public string $apellido = '';
    public string $email = '';
    public string $whatsapp = '';
    public ?int $edad = null;
    public ?float $peso = null;
    public ?float $estatura = null;
    public string $genero = '';
    public string $ciudad = '';
    public string $pais = 'Colombia';

    // Step 2: Goals & level
    public string $objetivo = '';
    public string $experiencia = '';
    public string $ubicacion_entrenamiento = '';
    public string $dias_disponibles = '';
    public string $lesion = '';
    public string $detalle_lesion = '';
    public string $motivacion = '';

    // Step 3: Payment reference
    public string $payment_reference = '';

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'whatsapp' => 'required|string|max:50',
                'edad' => 'required|integer|min:16|max:80',
                'peso' => 'required|numeric|min:30|max:300',
                'estatura' => 'required|numeric|min:100|max:250',
                'genero' => 'required|in:male,female,other',
                'ciudad' => 'required|string|max:100',
            ]);
        }

        if ($this->step === 2) {
            $this->validate([
                'objetivo' => 'required|string|max:500',
                'experiencia' => 'required|in:principiante,intermedio,avanzado',
                'ubicacion_entrenamiento' => 'required|in:gym,home,hybrid',
                'dias_disponibles' => 'required|in:3,4,5,6',
                'lesion' => 'required|in:si,no',
                'motivacion' => 'required|string|max:500',
            ]);
        }

        $this->step++;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function submit(): void
    {
        // Create/find client
        $client = Client::where('email', $this->email)->first();

        if (!$client) {
            // For RISE, we just record the enrollment - client creation happens on admin approval
            $this->submitted = true;
            return;
        }

        // Create RISE program entry
        $startDate = now()->addDays(7)->startOfWeek();
        RiseProgram::create([
            'client_id' => $client->id,
            'enrollment_date' => now(),
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->addWeeks(12),
            'experience_level' => $this->experiencia,
            'training_location' => $this->ubicacion_entrenamiento,
            'gender' => $this->genero,
            'status' => 'active',
        ]);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.public.rise-enrollment')
            ->layout('components.layouts.public');
    }
}
