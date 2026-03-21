<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\WompiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Payment result page.
     *
     * Wompi redirects back with ?id=<transaction_id> in the URL.
     * We look up the payment by reference or transaction_id and show the appropriate status.
     */
    public function result(Request $request, WompiService $wompi): View
    {
        $transactionId = $request->query('id');
        $ref = $request->query('ref');

        $payment = null;
        $estado = 'pendiente';
        $planName = '';
        $monto = '';
        $reference = '';

        // Try to find payment by Wompi transaction ID first, then by reference
        if ($transactionId) {
            // Verify with Wompi API to get real-time status
            $result = $wompi->getTransactionStatus($transactionId);

            if ($result['success']) {
                $wompiReference = $result['transaction']['reference'] ?? null;
                $wompiStatus = $result['status'] ?? 'PENDING';

                if ($wompiReference) {
                    $payment = Payment::where('wompi_reference', $wompiReference)->first();

                    if ($payment) {
                        // Update payment with latest status from Wompi
                        $newStatus = $wompi->mapWompiStatus($wompiStatus);
                        $payment->update([
                            'status' => $newStatus,
                            'wompi_transaction_id' => $transactionId,
                            'payment_method' => $result['payment_method'] ?? $payment->payment_method,
                        ]);
                    }
                }

                $estado = match ($wompiStatus) {
                    'APPROVED' => 'aprobado',
                    'DECLINED', 'ERROR' => 'rechazado',
                    'VOIDED' => 'anulado',
                    default => 'pendiente',
                };
            }
        } elseif ($ref) {
            $payment = Payment::where('wompi_reference', $ref)->first();
        }

        // Fill view data from payment record
        if ($payment) {
            $planName = $payment->plan?->label() ?? ucfirst($payment->plan ?? 'Plan');
            $monto = '$' . number_format((float) $payment->amount, 0, ',', '.') . ' ' . ($payment->currency ?? 'COP');
            $reference = $payment->wompi_reference ?? '';

            // Use the DB status if we didn't get it from Wompi API
            if (!$transactionId && $payment->status) {
                $estado = match ($payment->status->value ?? $payment->status) {
                    'approved' => 'aprobado',
                    'declined', 'error' => 'rechazado',
                    'voided' => 'anulado',
                    default => 'pendiente',
                };
            }
        }

        // Fallback to query params for backwards compatibility
        if (!$payment) {
            $estado = $request->query('estado', 'pendiente');
            $planName = $request->query('plan', '');
            $monto = $request->query('monto', '');
            $reference = $request->query('ref', '');
        }

        Log::info('Payment result page viewed', [
            'transaction_id' => $transactionId,
            'reference' => $reference,
            'estado' => $estado,
        ]);

        return view('public.pago-exitoso', compact(
            'estado',
            'planName',
            'monto',
            'reference',
        ));
    }
}
