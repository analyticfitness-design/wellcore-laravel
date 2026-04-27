<?php

namespace App\Actions;

use App\Enums\UserType;
use App\Models\AssignedPlan;
use App\Models\Payment;
use App\Models\WellcoreNotification;
use App\Services\PlanLockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Aplica la renovación de un cliente después de que Wompi aprueba el pago.
 *
 * Reglas:
 * - Extiende TODOS los planes activos del cliente (entrenamiento/nutricion/habitos/supl)
 *   30 días desde el actual expires_at (o desde hoy si ya expiró), preservando días pre-pagados.
 * - Si el cliente no tiene planes activos, crea una notificación de admin en vez de fallar silenciosamente.
 * - Invalida el cache de PlanLockService.
 *
 * Transaccional — o se actualizan todos los planes o ninguno.
 */
class ActivateRenewalAction
{
    public function __construct(private PlanLockService $lockService) {}

    public function execute(Payment $payment): ?AssignedPlan
    {
        if (! $payment->client_id) {
            return null;
        }

        // SECURITY: validar que el client_id en reference matchea el client_id del payment.
        // Reference format: RENEWAL-{client_id}-{hex}-{ts}
        if (preg_match('/^RENEWAL-(\d+)-/', (string) $payment->wompi_reference, $m)) {
            $refClientId = (int) $m[1];
            if ($refClientId !== (int) $payment->client_id) {
                \Log::critical('Renewal reference mismatch — possible attack', [
                    'payment_id' => $payment->id,
                    'payment_client' => $payment->client_id,
                    'reference_client' => $refClientId,
                ]);

                return null;
            }
        }

        return DB::transaction(function () use ($payment) {
            $today = Carbon::now()->toDateString();

            $activePlans = AssignedPlan::query()
                ->forClient($payment->client_id)
                ->active()
                ->get();

            if ($activePlans->isEmpty()) {
                WellcoreNotification::create([
                    'user_type' => UserType::Admin,
                    'user_id' => 1,
                    'type' => 'renewal_no_active_plan',
                    'title' => 'ALERTA: Renovación sin plan activo',
                    'body' => "Cliente #{$payment->client_id} pagó \${$payment->amount} pero no tiene planes activos. Asignar plan urgente.",
                    'link' => "/admin/clients/{$payment->client_id}",
                ]);
                \Log::error('Renewal without active plans', [
                    'payment_id' => $payment->id,
                    'client_id' => $payment->client_id,
                ]);

                return null;
            }

            // Extiende cada plan 30 días desde su expires_at actual (o desde hoy si ya expiró).
            // Esto preserva los días pre-pagados cuando el cliente renueva con anticipación.
            foreach ($activePlans as $plan) {
                $current = $plan->expires_at ? Carbon::parse($plan->expires_at) : Carbon::now();
                $base = $current->greaterThan(Carbon::now()) ? $current : Carbon::now();
                $newExpiresAt = $base->copy()->addDays(30)->toDateString();

                $plan->update([
                    'valid_from' => $current->lessThan(Carbon::now()) ? $today : $plan->valid_from,
                    'expires_at' => $newExpiresAt,
                ]);
            }

            $latest = AssignedPlan::query()
                ->forClient($payment->client_id)
                ->active()
                ->orderByDesc('expires_at')
                ->first();

            if ($payment->client) {
                $this->lockService->flushCache($payment->client);
            }

            return $latest;
        });
    }
}
