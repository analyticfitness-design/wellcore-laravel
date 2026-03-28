<?php

namespace App\Livewire\Admin;

use App\Mail\PlanInvitation;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin', ['title' => 'Enviar Invitacion'])]
class SendPlanInvitation extends Component
{
    public string $recipientName = '';
    public string $recipientEmail = '';
    public string $selectedPlan = 'metodo';

    // Status
    public bool $sending = false;
    public string $successMessage = '';
    public string $errorMessage = '';

    // History
    public array $sentHistory = [];

    protected array $plans = [
        'rise' => [
            'name' => 'RISE',
            'price' => '$99.900 COP',
            'type' => 'Pago unico · 30 dias',
            'color' => 'amber',
            'desc' => 'Programa de 30 dias para iniciar la transformacion',
        ],
        'esencial' => [
            'name' => 'Esencial',
            'price' => '$299.000 COP/mes',
            'type' => 'Mensual',
            'color' => 'blue',
            'desc' => 'Entrenamiento personalizado + protocolo de habitos',
        ],
        'metodo' => [
            'name' => 'Metodo',
            'price' => '$399.000 COP/mes',
            'type' => 'Mensual · Mas popular',
            'color' => 'red',
            'desc' => 'Entreno + nutricion + ajustes semanales con coach',
        ],
        'elite' => [
            'name' => 'Elite',
            'price' => '$549.000 COP/mes',
            'type' => 'Mensual · Premium',
            'color' => 'purple',
            'desc' => 'Todo incluido + check-ins 1:1 + analisis avanzados',
        ],
        'presencial' => [
            'name' => 'Presencial',
            'price' => '$450k-$650k COP/mes',
            'type' => 'Mensual · Bucaramanga',
            'color' => 'green',
            'desc' => 'Sesiones cara a cara + seguimiento digital',
        ],
    ];

    public function sendInvitation(): void
    {
        $this->validate([
            'recipientName' => 'required|string|max:255',
            'recipientEmail' => 'required|email|max:255',
            'selectedPlan' => 'required|in:rise,esencial,metodo,elite,presencial',
        ], [
            'recipientName.required' => 'El nombre es obligatorio.',
            'recipientEmail.required' => 'El email es obligatorio.',
            'recipientEmail.email' => 'Ingresa un email valido.',
            'selectedPlan.required' => 'Selecciona un plan.',
        ]);

        $this->sending = true;
        $this->successMessage = '';
        $this->errorMessage = '';

        try {
            Mail::to($this->recipientEmail)->send(
                new PlanInvitation(
                    recipientName: $this->recipientName,
                    planKey: $this->selectedPlan,
                )
            );

            $planName = $this->plans[$this->selectedPlan]['name'];
            $this->successMessage = "Invitacion del plan {$planName} enviada a {$this->recipientEmail}";

            // Add to session history
            $this->sentHistory[] = [
                'name' => $this->recipientName,
                'email' => $this->recipientEmail,
                'plan' => $planName,
                'time' => now()->format('H:i'),
            ];

            $this->reset(['recipientName', 'recipientEmail']);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al enviar: ' . $e->getMessage();
        } finally {
            $this->sending = false;
        }
    }

    public function selectPlan(string $plan): void
    {
        $this->selectedPlan = $plan;
    }

    public function render()
    {
        return view('livewire.admin.send-plan-invitation', [
            'plans' => $this->plans,
        ]);
    }
}
