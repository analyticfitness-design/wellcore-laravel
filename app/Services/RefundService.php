<?php

namespace App\Services;

use App\Models\Payment;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\Log;

class RefundService
{
    private const REFUND_WINDOW_DAYS = 7;

    public function isEligible(Payment $payment): array
    {
        $daysFromPayment = now()->diffInDays($payment->created_at);

        if ($payment->status !== PaymentStatus::Approved->value && $payment->status !== 'approved') {
            return ['eligible' => false, 'reason' => 'Solo se pueden reembolsar pagos aprobados.'];
        }

        if ($daysFromPayment > self::REFUND_WINDOW_DAYS) {
            return [
                'eligible' => false,
                'reason' => "El periodo de reembolso de " . self::REFUND_WINDOW_DAYS . " dias ha expirado. Contacta soporte.",
            ];
        }

        return [
            'eligible' => true,
            'days_remaining' => self::REFUND_WINDOW_DAYS - $daysFromPayment,
            'amount' => $payment->amount,
        ];
    }

    public function processRefund(Payment $payment): array
    {
        $eligibility = $this->isEligible($payment);

        if (!$eligibility['eligible']) {
            return $eligibility;
        }

        try {
            // Mark payment as refunded in our system
            $payment->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);

            Log::info('Refund processed', [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'client_id' => $payment->client_id,
            ]);

            return [
                'success' => true,
                'message' => 'Reembolso procesado. El monto sera devuelto en 3-5 dias habiles.',
                'amount' => $payment->amount,
            ];
        } catch (\Exception $e) {
            Log::error('Refund failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Error procesando reembolso. Contacta soporte.'];
        }
    }
}
