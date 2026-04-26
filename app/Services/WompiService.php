<?php

namespace App\Services;

use App\Actions\ActivateRenewalAction;
use App\Enums\PaymentStatus;
use App\Enums\PlanType;
use App\Enums\UserType;
use App\Mail\PaymentConfirmation;
use App\Mail\WelcomeMail;
use App\Models\Client;
use App\Models\PageVisit;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\WellcoreNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WompiService
{
    protected string $baseUrl;

    protected string $publicKey;

    protected string $privateKey;

    protected string $eventsSecret;

    protected string $integritySecret;

    protected bool $sandbox;

    public function __construct()
    {
        $this->sandbox = (bool) config('wellcore.wompi.sandbox', true);
        $this->baseUrl = $this->sandbox
            ? 'https://sandbox.wompi.co/v1'
            : config('wellcore.wompi.base_url', 'https://production.wompi.co/v1');
        $this->publicKey = config('wellcore.wompi.public_key', '');
        $this->privateKey = config('wellcore.wompi.private_key', '');
        $this->eventsSecret = config('wellcore.wompi.events_secret', '');
        $this->integritySecret = config('wellcore.wompi.integrity_secret', '');
    }

    /**
     * Get the public key (safe for frontend).
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Check if running in sandbox mode.
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * Generate a unique payment reference.
     */
    public function generateReference(string $prefix = 'WC'): string
    {
        return $prefix.'-'.strtoupper(bin2hex(random_bytes(4))).'-'.time();
    }

    /**
     * Generate integrity signature for Wompi widget.
     * Formula: SHA256(reference + amountInCents + currency + integritySecret)
     */
    public function generateIntegritySignature(string $reference, int $amountInCents, string $currency = 'COP'): string
    {
        $concatenated = $reference.$amountInCents.$currency.$this->integritySecret;

        return hash('sha256', $concatenated);
    }

    /**
     * Create a payment link via Wompi API.
     */
    public function createPaymentLink(array $data): array
    {
        $reference = $data['reference'] ?? $this->generateReference();
        $amountInCents = (int) ($data['amount_in_cents'] ?? 0);
        $currency = $data['currency'] ?? 'COP';
        $description = $data['description'] ?? 'Pago WellCore Fitness';
        $customerEmail = $data['customer_email'] ?? '';
        $customerName = $data['customer_name'] ?? '';
        $redirectUrl = $data['redirect_url'] ?? route('pago-confirmado');
        $expiresAt = $data['expires_at'] ?? now()->addHours(24)->toISOString();

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->privateKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/payment_links', [
                'name' => $description,
                'description' => $description,
                'single_use' => true,
                'collect_shipping' => false,
                'currency' => $currency,
                'amount_in_cents' => $amountInCents,
                'redirect_url' => $redirectUrl,
                'expires_at' => $expiresAt,
                'customer_data' => [
                    'customer_references' => [
                        ['label' => 'Referencia', 'is_required' => false],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $result = $response->json('data', []);

                $this->logEvent('payment_link.created', [
                    'reference' => $reference,
                    'link_id' => $result['id'] ?? null,
                    'amount_in_cents' => $amountInCents,
                    'currency' => $currency,
                    'customer_email' => $customerEmail,
                ]);

                return [
                    'success' => true,
                    'reference' => $reference,
                    'link_id' => $result['id'] ?? null,
                    'url' => $result['url'] ?? '#',
                    'data' => $result,
                ];
            }

            $this->logEvent('payment_link.error', [
                'reference' => $reference,
                'status_code' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'reference' => $reference,
                'error' => $response->json('error.message', 'Error al crear enlace de pago'),
                'url' => '#',
            ];
        } catch (\Throwable $e) {
            Log::error('WompiService::createPaymentLink failed', [
                'error' => $e->getMessage(),
                'reference' => $reference,
            ]);

            return [
                'success' => false,
                'reference' => $reference,
                'error' => 'Error de conexion con el servicio de pagos',
                'url' => '#',
            ];
        }
    }

    /**
     * Verify a transaction by reference.
     */
    public function verifyTransaction(string $reference): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->privateKey,
            ])->get($this->baseUrl.'/transactions', [
                'reference' => $reference,
            ]);

            if ($response->successful()) {
                $transactions = $response->json('data', []);

                if (empty($transactions)) {
                    return [
                        'success' => false,
                        'error' => 'No se encontro la transaccion',
                        'status' => null,
                    ];
                }

                // Get the most recent transaction for this reference
                $transaction = is_array($transactions) && isset($transactions[0])
                    ? $transactions[0]
                    : $transactions;

                $this->logEvent('transaction.verified', [
                    'reference' => $reference,
                    'transaction_id' => $transaction['id'] ?? null,
                    'status' => $transaction['status'] ?? 'UNKNOWN',
                ]);

                return [
                    'success' => true,
                    'transaction' => $transaction,
                    'status' => $transaction['status'] ?? 'UNKNOWN',
                    'transaction_id' => $transaction['id'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => 'Error al verificar la transaccion',
                'status' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('WompiService::verifyTransaction failed', [
                'error' => $e->getMessage(),
                'reference' => $reference,
            ]);

            return [
                'success' => false,
                'error' => 'Error de conexion',
                'status' => null,
            ];
        }
    }

    /**
     * Get transaction status by Wompi transaction ID.
     */
    public function getTransactionStatus(string $transactionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->privateKey,
            ])->get($this->baseUrl.'/transactions/'.$transactionId);

            if ($response->successful()) {
                $transaction = $response->json('data', []);

                return [
                    'success' => true,
                    'transaction' => $transaction,
                    'status' => $transaction['status'] ?? 'UNKNOWN',
                    'payment_method' => $transaction['payment_method_type'] ?? null,
                    'amount_in_cents' => $transaction['amount_in_cents'] ?? 0,
                    'currency' => $transaction['currency'] ?? 'COP',
                ];
            }

            return [
                'success' => false,
                'error' => 'Transaccion no encontrada',
                'status' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('WompiService::getTransactionStatus failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => false,
                'error' => 'Error de conexion',
                'status' => null,
            ];
        }
    }

    /**
     * Verify Wompi webhook signature.
     *
     * Wompi computes: SHA256(event.type + event.data.transaction.id + event.data.transaction.status +
     *                         event.data.transaction.amount_in_cents + event.timestamp + events_secret)
     */
    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        if (empty($signature) || empty($this->eventsSecret)) {
            return false;
        }

        $event = $payload['event'] ?? '';
        $transaction = $payload['data']['transaction'] ?? [];
        $timestamp = $payload['timestamp'] ?? '';

        $transactionId = $transaction['id'] ?? '';
        $status = $transaction['status'] ?? '';
        $amountInCents = $transaction['amount_in_cents'] ?? '';

        $concatenated = $event
            .$transactionId
            .$status
            .$amountInCents
            .$timestamp
            .$this->eventsSecret;

        $computed = hash('sha256', $concatenated);

        return hash_equals($computed, $signature);
    }

    /**
     * Handle incoming webhook notification.
     * Returns true if the payment was updated successfully.
     */
    public function handleWebhook(array $payload): bool
    {
        $event = $payload['event'] ?? '';
        $transaction = $payload['data']['transaction'] ?? [];
        $timestamp = $payload['timestamp'] ?? now()->toISOString();

        if (empty($transaction)) {
            $this->logEvent('webhook.empty_transaction', ['event' => $event]);

            return false;
        }

        $reference = $transaction['reference'] ?? null;
        $wompiStatus = $transaction['status'] ?? '';
        $transactionId = $transaction['id'] ?? null;
        $paymentMethodType = $transaction['payment_method_type'] ?? null;
        $amountInCents = $transaction['amount_in_cents'] ?? 0;

        $this->logEvent('webhook.received', [
            'event' => $event,
            'reference' => $reference,
            'transaction_id' => $transactionId,
            'wompi_status' => $wompiStatus,
            'amount_in_cents' => $amountInCents,
            'payment_method' => $paymentMethodType,
            'timestamp' => $timestamp,
        ]);

        // FIX P1.8: Whitelist only transaction.updated events — return 200 so Wompi does not retry.
        if ($event !== 'transaction.updated') {
            $this->logEvent('webhook.event_ignored', [
                'event' => $event,
                'transaction_id' => $transactionId,
            ]);
            Log::info('Wompi webhook: evento ignorado', ['event' => $event]);

            return true;
        }

        if (! $reference) {
            $this->logEvent('webhook.missing_reference', [
                'event' => $event,
                'transaction_id' => $transactionId,
            ]);

            return false;
        }

        $newStatus = $this->mapWompiStatus($wompiStatus);

        // FIX P0.4 + P0.2: SELECT FOR UPDATE inside transaction to serialize concurrent webhook retries.
        // Idempotency guard and amount validation also happen inside the lock.
        $result = DB::transaction(function () use ($reference, $newStatus, $transactionId, $paymentMethodType, $amountInCents) {
            // SELECT ... FOR UPDATE — only one concurrent request proceeds; the other waits and then
            // sees the already-approved status and exits via the idempotency guard below.
            $payment = Payment::where('wompi_reference', $reference)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                $this->logEvent('webhook.payment_not_found', ['reference' => $reference]);

                return false;
            }

            $oldStatus = $payment->status;

            // FIX P0.4: Idempotency — if already approved, silently acknowledge so Wompi stops retrying.
            if ($oldStatus === PaymentStatus::Approved) {
                $this->logEvent('webhook.duplicate_ignored', [
                    'reference' => $reference,
                    'payment_id' => $payment->id,
                ]);

                return true;
            }

            // FIX P0.2: Amount validation — tolerate up to 100 cents of rounding difference.
            $expectedAmountCents = (int) round((float) $payment->amount * 100);
            $receivedAmountCents = (int) $amountInCents;
            if (abs($receivedAmountCents - $expectedAmountCents) > 100) {
                $this->logEvent('webhook.amount_mismatch', [
                    'payment_id' => $payment->id,
                    'expected_cents' => $expectedAmountCents,
                    'received_cents' => $receivedAmountCents,
                ]);
                Log::critical('Wompi webhook amount mismatch', [
                    'payment_id' => $payment->id,
                    'expected' => $expectedAmountCents,
                    'received' => $receivedAmountCents,
                ]);

                return false;
            }

            $payment->update([
                'status' => $newStatus,
                'wompi_transaction_id' => $transactionId,
                'payment_method' => $paymentMethodType ?? $payment->payment_method,
            ]);

            if ($newStatus === PaymentStatus::Approved && $payment->client_id) {
                Client::where('id', $payment->client_id)->update(['status' => 'activo']);
            }

            $this->logEvent('webhook.payment_updated', [
                'payment_id' => $payment->id,
                'reference' => $reference,
                'old_status' => $oldStatus instanceof PaymentStatus ? $oldStatus->value : $oldStatus,
                'new_status' => $newStatus->value,
                'wompi_status' => $wompiStatus,
                'transaction_id' => $transactionId,
            ]);

            // FIX P0.4: Schedule post-approval automation after the transaction commits,
            // ensuring it runs exactly once even when two concurrent requests enter simultaneously.
            if ($newStatus === PaymentStatus::Approved) {
                DB::afterCommit(fn () => $this->runPostApprovalAutomation($payment->fresh()));
            }

            return $payment;
        });

        if ($result === false) {
            return false;
        }

        return true;
    }

    /**
     * Run all post-payment-approval automations:
     * - Send welcome email to client
     * - Send payment confirmation email
     * - Award WellCoins for first payment
     * - Create admin notification
     * - Audit log the event
     */
    protected function runPostApprovalAutomation(Payment $payment): void
    {
        try {
            $client = $payment->client_id ? Client::find($payment->client_id) : null;

            // 0. Renewal? Extend the plan and skip welcome/first-timer flows.
            if ($payment->isRenewal()) {
                $renewalAction = app(ActivateRenewalAction::class);
                $newPlan = $renewalAction->execute($payment);

                $this->logEvent('webhook.renewal_activated', [
                    'payment_id' => $payment->id,
                    'client_id' => $payment->client_id,
                    'new_plan_id' => $newPlan?->id,
                    'expires_at' => $newPlan?->expires_at?->toDateString(),
                ]);

                // Send renewal confirmation email (reuse PaymentConfirmation, skip WelcomeMail).
                $recipientEmail = $client?->email ?? $payment->email ?? null;
                if ($recipientEmail) {
                    $planName = $payment->plan instanceof PlanType
                        ? $payment->plan->value
                        : ($payment->plan ?? 'Plan WellCore');

                    Mail::to($recipientEmail)->queue(new PaymentConfirmation(
                        clientName: $client?->name ?? $payment->buyer_name ?? 'Cliente',
                        amount: number_format((float) $payment->amount, 0, '.', '.'),
                        currency: $payment->currency ?? 'COP',
                        plan: $planName.' · Renovación',
                        reference: $payment->wompi_reference ?? (string) $payment->id,
                    ));
                }

                // Notify admin
                WellcoreNotification::create([
                    'user_type' => UserType::Admin,
                    'user_id' => 1,
                    'type' => 'renewal_approved',
                    'title' => 'Renovación aprobada',
                    'body' => 'Cliente #'.$payment->client_id.' renovó su plan por $'
                        .number_format((float) $payment->amount, 0, '.', '.').' '.($payment->currency ?? 'COP'),
                ]);

                return;
            }

            // 1. Send welcome email to client (solo para primer pago, no para renovaciones)
            if ($client && $client->email) {
                $planName = $payment->plan instanceof PlanType
                    ? $payment->plan->value
                    : ($payment->plan ?? 'Esencial');

                Mail::to($client->email)->queue(new WelcomeMail(
                    clientName: $client->name ?? 'Cliente',
                    planName: $planName,
                    coachName: 'Tu coach asignado',
                ));
            }

            // 2. Send payment confirmation email
            $recipientEmail = $client?->email ?? $payment->email ?? null;
            if ($recipientEmail) {
                $planName = $payment->plan instanceof PlanType
                    ? $payment->plan->value
                    : ($payment->plan ?? 'Plan WellCore');

                Mail::to($recipientEmail)->queue(new PaymentConfirmation(
                    clientName: $client?->name ?? $payment->buyer_name ?? 'Cliente',
                    amount: number_format((float) $payment->amount, 0, '.', '.'),
                    currency: $payment->currency ?? 'COP',
                    plan: $planName,
                    reference: $payment->wompi_reference ?? $payment->payu_reference ?? (string) $payment->id,
                ));
            }

            // 3. Award WellCoins for first payment
            if ($payment->client_id) {
                WellCoinsService::earn($payment->client_id, 'first_checkin', 'Primer pago completado');
            }

            // 4. Create admin notification
            WellcoreNotification::create([
                'user_type' => UserType::Admin,
                'user_id' => 1,
                'type' => 'payment_approved',
                'title' => 'Pago Aprobado',
                'body' => 'Cliente #'.$payment->client_id.' completó pago de $'
                    .number_format((float) $payment->amount, 0, '.', '.').' '.($payment->currency ?? 'COP'),
            ]);

            // 5. Audit log
            AuditService::logAction(
                'payment_approved',
                'Payment #'.($payment->wompi_reference ?? $payment->id).' aprobado para cliente #'.$payment->client_id,
            );

            $this->logEvent('webhook.post_approval_done', [
                'payment_id' => $payment->id,
                'client_id' => $payment->client_id,
                'reference' => $payment->wompi_reference,
            ]);

            // 6. Meta Conversions API: server-side Purchase event
            if (MetaConversionsService::isConfigured()) {
                app(MetaConversionsService::class)->trackPurchase($payment);
            }

            // 7. Update page visit conversion (UTM attribution)
            $clientId = $payment->client_id;
            if ($clientId) {
                PageVisit::where('client_id', $clientId)
                    ->whereNull('converted_at')
                    ->latest()
                    ->limit(1)
                    ->update([
                        'payment_id' => $payment->id,
                        'converted_at' => now(),
                        'conversion_type' => 'payment',
                    ]);
            }
        } catch (\Throwable $e) {
            Log::error('WompiService::runPostApprovalAutomation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Map Wompi transaction status to our PaymentStatus enum.
     */
    public function mapWompiStatus(string $wompiStatus): PaymentStatus
    {
        return match ($wompiStatus) {
            'APPROVED' => PaymentStatus::Approved,
            'DECLINED' => PaymentStatus::Declined,
            'VOIDED' => PaymentStatus::Voided,
            'ERROR' => PaymentStatus::Error,
            'PENDING' => PaymentStatus::Pending,
            default => PaymentStatus::Pending,
        };
    }

    /**
     * Get Wompi merchant data (acceptance tokens, presigned acceptance, etc.).
     */
    public function getMerchantInfo(): array
    {
        try {
            $response = Http::get($this->baseUrl.'/merchants/'.$this->publicKey);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data', []),
                ];
            }

            return ['success' => false, 'error' => 'Error obteniendo datos del comercio'];
        } catch (\Throwable $e) {
            Log::error('WompiService::getMerchantInfo failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'error' => 'Error de conexion'];
        }
    }

    /**
     * Get acceptance token (required for Wompi widget).
     */
    public function getAcceptanceToken(): ?string
    {
        $merchant = $this->getMerchantInfo();

        if ($merchant['success']) {
            return $merchant['data']['presigned_acceptance']['acceptance_token'] ?? null;
        }

        return null;
    }

    /**
     * Void/reverse a transaction.
     */
    public function voidTransaction(string $transactionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->privateKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/transactions/'.$transactionId.'/void');

            if ($response->successful()) {
                $this->logEvent('transaction.voided', [
                    'transaction_id' => $transactionId,
                ]);

                return [
                    'success' => true,
                    'data' => $response->json('data', []),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Error al anular transaccion'),
            ];
        } catch (\Throwable $e) {
            Log::error('WompiService::voidTransaction failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            return ['success' => false, 'error' => 'Error de conexion'];
        }
    }

    /**
     * Prepare widget data for frontend (safe, no private keys exposed).
     */
    public function getWidgetData(string $reference, int $amountInCents, string $currency = 'COP', string $redirectUrl = ''): array
    {
        return [
            'public_key' => $this->publicKey,
            'currency' => $currency,
            'amount_in_cents' => $amountInCents,
            'reference' => $reference,
            'signature' => $this->generateIntegritySignature($reference, $amountInCents, $currency),
            'redirect_url' => $redirectUrl ?: route('pago-confirmado'),
            'sandbox' => $this->sandbox,
        ];
    }

    /**
     * Determina si una referencia Wompi pertenece a una invitación de coach.
     * Prefijo: "WCI-" (coach invitation).
     * Referencia normal: "WC-XXXXXXXX-TIMESTAMP"
     * Referencia renovación: "RENEWAL-..."
     */
    public function isCoachInvitationReference(string $reference): bool
    {
        return str_starts_with($reference, 'WCI-');
    }

    /**
     * Extrae el invitation code de una referencia WCI-{code}.
     * Ejemplo: "WCI-a1b2c3d4e5f6..." → "a1b2c3d4e5f6..."
     */
    public function extractInvitationCode(string $reference): string
    {
        return substr($reference, 4);
    }

    /**
     * Log a payment event for auditing.
     */
    protected function logEvent(string $event, array $data = []): void
    {
        try {
            PaymentLog::create([
                'event' => $event,
                'reference' => $data['reference'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_id' => $data['payment_id'] ?? null,
                'status' => $data['wompi_status'] ?? ($data['status'] ?? null),
                'payload' => $data,
            ]);
        } catch (\Throwable $e) {
            // Fallback to Laravel log if DB write fails
            Log::info('WompiEvent: '.$event, $data);
        }

        Log::channel('single')->info('Wompi: '.$event, $data);
    }
}
