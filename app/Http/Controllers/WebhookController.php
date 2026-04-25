<?php

namespace App\Http\Controllers;

use App\Enums\CoachInvitationStatus;
use App\Models\CoachInvitation;
use App\Models\Payment;
use App\Services\CoachInvitationService;
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
            'has_signature' => ! empty($signature),
            'ip' => $request->ip(),
        ]);

        // Verify webhook signature
        if (! $wompi->verifyWebhookSignature($payload, $signature)) {
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
            $reference = $payload['data']['transaction']['reference'] ?? null;
            $wompiStatus = $payload['data']['transaction']['status'] ?? '';

            // --- Pre-hook: Coach Invitation ---
            // Corre ANTES de handleWebhook() para que payment.client_id esté seteado
            // cuando runPostApprovalAutomation() intente enviar el WelcomeMail.
            if ($reference && $wompi->isCoachInvitationReference($reference) && $wompiStatus === 'APPROVED') {
                $code = $wompi->extractInvitationCode($reference);
                $coachInvitation = CoachInvitation::where('code', $code)->first();

                if ($coachInvitation && $coachInvitation->status !== CoachInvitationStatus::Paid) {
                    $payment = Payment::where('wompi_reference', $reference)->first();
                    if ($payment) {
                        try {
                            app(CoachInvitationService::class)->handlePaymentApproved($payment, $coachInvitation);
                        } catch (\Throwable $e) {
                            // Log but do NOT block the main webhook processing
                            Log::error('CoachInvitation pre-hook failed', [
                                'code' => $code,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }
            // --- Fin pre-hook ---

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
