<?php

namespace App\Livewire;

use App\Enums\PaymentStatus;
use App\Enums\PlanType;
use App\Models\Payment;
use App\Services\WompiService;
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

    // Payment (Step 3) - generated server-side
    public string $paymentReference = '';
    public string $wompiPublicKey = '';
    public string $wompiSignature = '';
    public string $wompiRedirectUrl = '';
    public bool $wompiSandbox = true;
    public int $amountInCents = 0;
    public string $currency = 'COP';

    // Status
    public string $paymentError = '';

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
        $this->paymentError = '';
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
            'email.email' => 'Ingresa un email valido.',
            'whatsapp.required' => 'Tu WhatsApp es requerido.',
            'objetivo.required' => 'Selecciona tu objetivo.',
            'terminos.accepted' => 'Debes aceptar los terminos.',
        ]);

        $this->prepareWompiPayment();
        $this->step = 3;
    }

    /**
     * Prepare payment data server-side for the Wompi widget.
     * Private keys and integrity secret NEVER go to the frontend.
     */
    protected function prepareWompiPayment(): void
    {
        $wompi = app(WompiService::class);

        $total = $this->getTotal();
        $this->amountInCents = $total * 100; // COP is already in whole pesos; Wompi needs cents
        $this->paymentReference = $wompi->generateReference();
        $this->currency = 'COP';

        $widgetData = $wompi->getWidgetData(
            $this->paymentReference,
            $this->amountInCents,
            $this->currency,
            route('pago-confirmado'),
        );

        $this->wompiPublicKey = $widgetData['public_key'];
        $this->wompiSignature = $widgetData['signature'];
        $this->wompiRedirectUrl = $widgetData['redirect_url'];
        $this->wompiSandbox = $widgetData['sandbox'];

        // Create the payment record in pending status
        Payment::create([
            'email' => $this->email,
            'buyer_name' => $this->nombre,
            'buyer_phone' => $this->whatsapp,
            'plan' => $this->plan,
            'amount' => $total,
            'currency' => $this->currency,
            'status' => PaymentStatus::Pending,
            'wompi_reference' => $this->paymentReference,
            'payment_method' => 'wompi',
        ]);

        $this->paymentError = '';
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
