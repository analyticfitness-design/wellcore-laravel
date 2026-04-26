<?php

namespace App\Livewire;

use App\Auth\WellCoreGuard;
use App\Enums\PaymentStatus;
use App\Enums\PlanType;
use App\Models\Client;
use App\Models\Payment;
use App\Services\PricingService;
use App\Services\WompiService;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Checkout extends Component
{
    public int $step = 1;

    // Step 1: Plan
    public string $plan = '';

    #[Locked]
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

    #[Locked]
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

    // Renovación: cuando el cliente viene de /renovar en lugar de /pagar
    public bool $isRenewal = false;

    #[Locked]
    public ?int $renewalClientId = null;

    /**
     * Plans se cargan desde config/plans.php (SSOT).
     * NO hardcodear precios aquí — cambiar en config/plans.php.
     */
    protected function getPlans(): array
    {
        $plans = [];
        foreach (['rise', 'esencial', 'metodo', 'elite'] as $key) {
            $cfg = app(PricingService::class)->configFor($key);
            $plans[$key] = [
                'name' => $cfg['name'],
                'price' => (int) $cfg['price_cop'],
                'desc' => $cfg['desc'] ?? '',
            ];
        }

        return $plans;
    }

    public function mount(): void
    {
        $plans = $this->getPlans();

        // Renovación: viene del flujo /renovar (path) o query renewal=1 y hay cliente autenticado.
        if (request()->is('renovar') || request()->boolean('renewal')) {
            $this->isRenewal = true;
            $this->prefillFromAuthenticatedClient();
        }

        if (request()->has('plan') && array_key_exists(request('plan'), $plans)) {
            $this->selectPlan(request('plan'));
        }
    }

    /**
     * Pre-llena datos del cliente autenticado cuando es una renovación.
     * Así no tiene que re-ingresar nombre/email/whatsapp.
     */
    protected function prefillFromAuthenticatedClient(): void
    {
        try {
            $guard = app(WellCoreGuard::class);
            $user = $guard->user();

            if (! $user instanceof Client) {
                return;
            }

            $this->renewalClientId = $user->id;
            $this->nombre = $user->name ?? '';
            $this->email = $user->email ?? '';
            $this->whatsapp = $user->phone ?? '';

            // Pre-selecciona el plan actual del cliente
            $planValue = $user->plan instanceof PlanType
                ? $user->plan->value
                : (string) ($user->plan ?? '');

            if (in_array($planValue, ['esencial', 'metodo', 'elite'], true)) {
                $this->selectPlan($planValue);
            }
        } catch (\Throwable) {
            // Sin cliente autenticado, continúa como guest
        }
    }

    public function selectPlan(string $plan): void
    {
        $plans = $this->getPlans();
        if (! array_key_exists($plan, $plans)) {
            return;
        }
        $this->plan = $plan;
        $this->price = $plans[$plan]['price'];
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
        $codigos = ['WELLCORE10' => 10, 'RISE20' => 20, 'FITSTART' => 15];
        $code = strtoupper(trim($this->codigoDescuento));
        $codePct = $codigos[$code] ?? 0;

        if ($codePct === 0) {
            $this->descuento = 0;
            $this->descuentoMensaje = 'Codigo no valido';
            return;
        }

        // P2.1: "best discount wins" — comparar promo% vs código%, aplicar el mayor sobre price_original.
        // Evita apilar descuentos (código 10% + promo 15% ≠ 25%).
        $pricing = app(PricingService::class);
        $original = $pricing->originalPriceFor($this->plan);
        $promoPct = $pricing->discountPercent($this->plan);
        $bestPct = max($codePct, $promoPct);

        $newPrice = (int) round($original * (100 - $bestPct) / 100);
        $this->price = $newPrice;
        $this->descuento = 0; // ya absorbido en $price
        $this->descuentoMensaje = "Descuento {$bestPct}% aplicado";
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

        // SECURITY: re-resolver cliente desde guard. NO confiar en property serializada.
        if ($this->isRenewal) {
            $guard = app(WellCoreGuard::class);
            $authedClient = $guard->user();

            if (! $authedClient instanceof Client) {
                $this->paymentError = 'Sesion expirada. Inicia sesion para renovar.';

                return;
            }

            // SOBRESCRIBIR siempre — el guard es la única fuente de verdad
            $this->renewalClientId = $authedClient->id;
        }

        // SECURITY: recomputar precio canónico desde config, no de las props public (manipulables)
        $canonicalPrice = app(PricingService::class)->priceFor($this->plan);
        if ($canonicalPrice <= 0) {
            $this->paymentError = 'Plan invalido.';

            return;
        }

        $finalDiscount = $this->resolveServerSideDiscount($canonicalPrice);
        $total = max(1500, $canonicalPrice - $finalDiscount); // mínimo 1500 COP

        $this->price = $canonicalPrice;
        $this->descuento = $finalDiscount;
        $this->amountInCents = $total * 100;

        // Renovación: prefijo RENEWAL-{clientId}-{timestamp} para distinguir en analytics
        // y que el webhook sepa activar el plan via ActivateRenewalAction.
        if ($this->isRenewal && $this->renewalClientId) {
            // P2.8: 128-bit entropy (16 bytes hex) en lugar de 32-bit (4 bytes).
            $this->paymentReference = sprintf(
                'RENEWAL-%d-%s-%d',
                $this->renewalClientId,
                strtoupper(bin2hex(random_bytes(16))),
                time()
            );
        } else {
            $this->paymentReference = $wompi->generateReference();
        }

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
            'client_id' => $this->renewalClientId, // null para checkout normal; cliente autenticado para renovación
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

    protected function resolveServerSideDiscount(int $canonicalPrice): int
    {
        if (! $this->codigoDescuento) {
            return 0;
        }
        $codigos = ['WELLCORE10' => 10, 'RISE20' => 20, 'FITSTART' => 15];
        $code = strtoupper(trim($this->codigoDescuento));
        if (! isset($codigos[$code])) {
            return 0;
        }

        return (int) round($canonicalPrice * $codigos[$code] / 100);
    }

    public function getPlanInfo(): array
    {
        $plans = $this->getPlans();

        return $plans[$this->plan] ?? ['name' => '', 'price' => 0, 'desc' => ''];
    }

    public function render()
    {
        return view('livewire.checkout', [
            'planInfo' => $this->getPlanInfo(),
            'total' => $this->getTotal(),
            'allPlans' => $this->getPlans(),
        ])->layout('components.layouts.public', [
            'title' => 'Pagar - WellCore Fitness',
            'description' => 'Completa tu pago y comienza tu transformacion con WellCore Fitness.',
        ]);
    }
}
