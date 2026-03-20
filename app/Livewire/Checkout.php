<?php

namespace App\Livewire;

use Livewire\Component;

class Checkout extends Component
{
    public int $step = 1;

    // Step 1: Plan
    public string $plan = '';
    public int $price = 0;

    // Step 2: User data
    public string $nombre = '';
    public string $email = '';
    public string $whatsapp = '';
    public string $pais = 'colombia';
    public string $objetivo = '';
    public bool $terminos = false;

    // Discount
    public string $codigoDescuento = '';
    public int $descuento = 0;
    public string $descuentoMensaje = '';

    protected array $plans = [
        'esencial' => ['name' => 'Esencial', 'price' => 299000, 'desc' => 'Entrenamiento personalizado + guia nutricional basica'],
        'metodo' => ['name' => 'Metodo', 'price' => 399000, 'desc' => 'Entreno + Nutricion + Ajustes semanales con coach'],
        'elite' => ['name' => 'Elite', 'price' => 549000, 'desc' => 'Todo incluido + Check-ins 1:1 + Protocolo habitos'],
    ];

    public function mount(): void
    {
        if (request()->has('plan') && array_key_exists(request('plan'), $this->plans)) {
            $this->selectPlan(request('plan'));
        }
    }

    public function selectPlan(string $plan): void
    {
        $this->plan = $plan;
        $this->price = $this->plans[$plan]['price'];
        $this->step = 2;
    }

    public function goToStep(int $step): void
    {
        if ($step === 1 || ($step === 2 && $this->plan) || ($step === 3 && $this->plan && $this->nombre)) {
            $this->step = $step;
        }
    }

    public function aplicarDescuento(): void
    {
        $codigos = [
            'WELLCORE10' => 10,
            'RISE20' => 20,
            'FITSTART' => 15,
        ];

        $code = strtoupper(trim($this->codigoDescuento));
        if (isset($codigos[$code])) {
            $pct = $codigos[$code];
            $this->descuento = (int) round($this->price * $pct / 100);
            $this->descuentoMensaje = "Descuento {$pct}% aplicado";
        } else {
            $this->descuento = 0;
            $this->descuentoMensaje = 'Codigo no valido';
        }
    }

    public function proceedToPayment(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email',
            'whatsapp' => 'required|string',
            'pais' => 'required|string',
            'objetivo' => 'required|string',
            'terminos' => 'accepted',
        ], [
            'nombre.required' => 'Tu nombre es requerido.',
            'email.required' => 'Tu email es requerido.',
            'whatsapp.required' => 'Tu WhatsApp es requerido.',
            'objetivo.required' => 'Selecciona tu objetivo.',
            'terminos.accepted' => 'Debes aceptar los terminos.',
        ]);

        $this->step = 3;
    }

    public function getTotal(): int
    {
        return max(0, $this->price - $this->descuento);
    }

    public function getPlanInfo(): array
    {
        return $this->plans[$this->plan] ?? ['name' => '', 'price' => 0, 'desc' => ''];
    }

    public function render()
    {
        return view('livewire.checkout', [
            'planInfo' => $this->getPlanInfo(),
            'total' => $this->getTotal(),
            'allPlans' => $this->plans,
        ])->layout('components.layouts.public', [
            'title' => 'Pagar - WellCore Fitness',
            'description' => 'Completa tu pago y comienza tu transformacion con WellCore Fitness.',
        ]);
    }
}
