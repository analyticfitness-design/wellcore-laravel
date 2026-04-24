<?php

namespace App\Actions;

use App\Models\AssignedPlan;
use App\Models\Payment;
use App\Services\PlanLockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Aplica la renovación de un cliente después de que Wompi aprueba el pago.
 *
 * Reglas:
 * - Extiende TODOS los planes activos del cliente (entrenamiento/nutricion/habitos/supl)
 *   30 días más desde hoy, actualizando valid_from y expires_at.
 * - Si el cliente no tenía ningún plan, no hace nada (el coach tendrá que asignar).
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

        return DB::transaction(function () use ($payment) {
            $today = Carbon::now()->toDateString();
            $newExpiresAt = Carbon::now()->addDays(30)->toDateString();

            // Actualiza TODOS los planes asignados activos del cliente con nuevas fechas.
            // No los reemplaza (preserva contenido del coach); solo extiende fechas.
            AssignedPlan::query()
                ->forClient($payment->client_id)
                ->active()
                ->update([
                    'valid_from' => $today,
                    'expires_at' => $newExpiresAt,
                ]);

            // Retorna el plan más reciente para logs / email de confirmación.
            $latest = AssignedPlan::query()
                ->forClient($payment->client_id)
                ->active()
                ->orderByDesc('expires_at')
                ->first();

            // Flush cache del lock service — el próximo request del cliente verá el plan activo.
            if ($payment->client) {
                $this->lockService->flushCache($payment->client);
            }

            return $latest;
        });
    }
}
