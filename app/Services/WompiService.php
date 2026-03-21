<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Enums\PaymentStatus;

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
        return $prefix . '-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
    }

    /**
     * Generate integrity signature for Wompi widget.
     * Formula: SHA256(reference + amountInCents + currency + integritySecret)
     */
    public function generateIntegritySignature(string $reference, int $amountInCents, string $currency = 'COP'): string
    {
        $concatenated = $reference . $amountInCents . $currency . $this->integritySecret;

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
                'Authorization' => 'Bearer ' . $this->privateKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payment_links', [
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
                'Authorization' => 'Bearer ' . $this->privateKey,
            ])->get($this->baseUrl . '/transactions', [
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
                'Authorization' => 'Bearer ' . $this->privateKey,
            ])->get($this->baseUrl . '/transactions/' . $transactionId);

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
            . $transactionId
            . $status
            . $amountInCents
            . $timestamp
            . $this->eventsSecret;

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

        if ($event !== 'transaction.updated' || !$reference) {
            return false;
        }

        $payment = Payment::where('wompi_reference', $reference)->first();

        if (!$payment) {
            $this->logEvent('webhook.payment_not_found', [
                'reference' => $reference,
                'transaction_id' => $transactionId,
            ]);
            return false;
        }

        $newStatus = $this->mapWompiStatus($wompiStatus);
        $oldStatus = $payment->status;

        $payment->update([
            'status' => $newStatus,
            'wompi_transaction_id' => $transactionId,
            'payment_method' => $paymentMethodType ?? $payment->payment_method,
        ]);

        $this->logEvent('webhook.payment_updated', [
            'payment_id' => $payment->id,
            'reference' => $reference,
            'old_status' => $oldStatus instanceof PaymentStatus ? $oldStatus->value : $oldStatus,
            'new_status' => $newStatus->value,
            'wompi_status' => $wompiStatus,
            'transaction_id' => $transactionId,
        ]);

        return true;
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
            $response = Http::get($this->baseUrl . '/merchants/' . $this->publicKey);

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
                'Authorization' => 'Bearer ' . $this->privateKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/transactions/' . $transactionId . '/void');

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
            Log::info('WompiEvent: ' . $event, $data);
        }

        Log::channel('single')->info('Wompi: ' . $event, $data);
    }
}
