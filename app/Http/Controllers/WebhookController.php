<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\WompiService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function wompi(Request $request, WompiService $wompi): \Illuminate\Http\JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Event-Signature', '');

        if (!$wompi->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $data = $request->json()->all();
        $event = $data['event'] ?? '';
        $transaction = $data['data']['transaction'] ?? [];

        if ($event === 'transaction.updated' && isset($transaction['reference'])) {
            $payment = Payment::where('wompi_reference', $transaction['reference'])->first();
            if ($payment) {
                $payment->update([
                    'status' => match ($transaction['status'] ?? '') {
                        'APPROVED' => 'approved',
                        'DECLINED' => 'declined',
                        'VOIDED' => 'voided',
                        'ERROR' => 'error',
                        default => $payment->status,
                    },
                    'wompi_transaction_id' => $transaction['id'] ?? null,
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }
}
