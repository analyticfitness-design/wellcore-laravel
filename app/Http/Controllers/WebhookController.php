<?php

namespace App\Http\Controllers;

use App\Services\WompiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Wompi webhook notifications.
     *
     * Events handled:
     * - transaction.updated: Payment status changed (APPROVED, DECLINED, VOIDED, ERROR, PENDING)
     *
     * The webhook endpoint is excluded from CSRF verification in bootstrap/app.php.
     */
    public function wompi(Request $request, WompiService $wompi): JsonResponse
    {
        $payload = $request->json()->all();
        $signature = $request->header('X-Event-Checksum', '');

        Log::info('Wompi webhook received', [
            'event' => $payload['event'] ?? 'unknown',
            'has_signature' => !empty($signature),
            'ip' => $request->ip(),
        ]);

        // Verify webhook signature
        if (!$wompi->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Wompi webhook: invalid signature', [
                'ip' => $request->ip(),
                'event' => $payload['event'] ?? 'unknown',
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Firma invalida',
            ], 403);
        }

        // Process the webhook event
        $event = $payload['event'] ?? '';

        if ($event === 'transaction.updated') {
            $processed = $wompi->handleWebhook($payload);

            return response()->json([
                'status' => $processed ? 'ok' : 'ignored',
                'message' => $processed
                    ? 'Transaccion actualizada'
                    : 'Transaccion no encontrada o evento no aplicable',
            ]);
        }

        // Handle nequi.token.updated or other future events
        if ($event === 'nequi_token.updated') {
            Log::info('Wompi webhook: nequi token event', [
                'data' => $payload['data'] ?? [],
            ]);

            return response()->json(['status' => 'ok', 'message' => 'Evento Nequi recibido']);
        }

        // Unknown event type - acknowledge receipt
        Log::info('Wompi webhook: unhandled event type', ['event' => $event]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Evento recibido',
        ]);
    }
}
