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
 * - Marca TODOS los planes activos del cliente como inactivos.
 * - Crea un nuevo AssignedPlan con valid_from=hoy, active=true.
 * - El hook `creating` del modelo AssignedPlan calcula expires_at = hoy + 30 días.
 * - Invalida el cache de PlanLockService.
 *
 * Transaccional para no dejar el cliente en estado inconsistente.
 */
class ActivateRenewalAction
{
    public function __construct(private PlanLockService $lockService) {}

    public function execute(Payment $payment): ?AssignedPlan
    {
        if (! $payment->client_id) {
            return null;
        }

        $planType = $payment->plan?->value ?? null;

        if (! $planType) {
            return null;
        }

        return DB::transaction(function () use ($payment, $planType) {
            // Preservar contenido del plan anterior para que el coach no pierda el plan subido.
            $previous = AssignedPlan::query()
                ->forClient($payment->client_id)
                ->where('plan_type', $planType)
                ->active()
                ->latest('id')
                ->first();

            // Desactivar planes previos del mismo tipo.
            AssignedPlan::query()
                ->forClient($payment->client_id)
                ->where('plan_type', $planType)
                ->active()
                ->update(['active' => false]);

            $new = AssignedPlan::create([
                'client_id' => $payment->client_id,
                'plan_type' => $planType,
                'content' => $previous?->content ?? ['renewed' => true],
                'version' => ($previous?->version ?? 0) + 1,
                'valid_from' => Carbon::now()->toDateString(),
                'active' => true,
                'assigned_by' => $previous?->assigned_by,
                // expires_at se calcula automáticamente en el hook `creating` del modelo.
            ]);

            // Flush cache del lock service — el próximo request del cliente verá el plan activo.
            if ($payment->client) {
                $this->lockService->flushCache($payment->client);
            }

            return $new;
        });
    }
}
