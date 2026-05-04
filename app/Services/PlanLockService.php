<?php

namespace App\Services;

use App\Enums\PlanType;
use App\Models\AssignedPlan;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Decide si un cliente tiene el acceso bloqueado por plan expirado.
 *
 * Reglas:
 *  - Si el cliente no tiene plan activo → no está lockeado (aún no empieza)
 *  - Si el plan no tiene expires_at → no está lockeado (trial, rise, legacy)
 *  - Si hoy > expires_at → lockeado
 *  - Grace period: de día 25 a día 29 el cliente ve warning pero aún no está locked
 *  - Día 30+ → locked, debe renovar
 */
class PlanLockService
{
    private const GRACE_DAYS_BEFORE_EXPIRY = 5;

    private const CACHE_TTL_SECONDS = 300; // 5 min

    /**
     * Obtiene el assigned_plan activo más reciente del cliente (cualquier tipo:
     * nutricion, entrenamiento, habitos, suplementacion). El expires_at de ese
     * plan determina cuándo expira la suscripción del cliente.
     *
     * Retorna null si el cliente NO está en un plan mensual (esencial/metodo/elite),
     * porque esos planes no se lockean (rise/presencial/trial siguen flujos distintos).
     */
    public function getActivePlan(Client $client): ?AssignedPlan
    {
        if (! $this->isMonthlyPlan($client)) {
            return null;
        }

        return AssignedPlan::query()
            ->forClient($client->id)
            ->active()
            ->whereNotNull('expires_at')
            ->orderBy('expires_at', 'asc')  // plan que expira ANTES dispara el lock primero
            ->first();
    }

    /**
     * ¿El cliente tiene un plan mensual sujeto al lock de 30 días?
     * Aplica a: esencial, metodo, elite, entreno_solo, nutricion_solo.
     * NO aplica a: rise (one-time 30 días), presencial (rango), trial (3 días).
     */
    private function isMonthlyPlan(Client $client): bool
    {
        return in_array($this->clientPlanValue($client), ['esencial', 'metodo', 'elite', 'entreno_solo', 'nutricion_solo'], true);
    }

    public function isLocked(Client $client): bool
    {
        $plan = $this->getActivePlan($client);

        if (! $plan) {
            return false;
        }

        if (! $plan->expires_at) {
            return false;
        }

        return $plan->isExpired();
    }

    public function daysUntilExpiry(Client $client): ?int
    {
        $plan = $this->getActivePlan($client);

        return $plan?->daysUntilExpiry();
    }

    public function expiresAt(Client $client): ?Carbon
    {
        $plan = $this->getActivePlan($client);

        return $plan?->expires_at ? Carbon::parse($plan->expires_at) : null;
    }

    public function isInGracePeriod(Client $client): bool
    {
        $days = $this->daysUntilExpiry($client);

        if ($days === null) {
            return false;
        }

        return $days > 0 && $days <= self::GRACE_DAYS_BEFORE_EXPIRY;
    }

    /**
     * Estado completo para el frontend. Cached 5 min para evitar queries en cada request.
     */
    public function status(Client $client): array
    {
        return Cache::remember(
            "plan_lock_status:{$client->id}",
            self::CACHE_TTL_SECONDS,
            fn () => $this->computeStatus($client)
        );
    }

    public function flushCache(Client $client): void
    {
        Cache::forget("plan_lock_status:{$client->id}");
    }

    private function computeStatus(Client $client): array
    {
        if (! $this->isMonthlyPlan($client)) {
            return [
                'client_id' => $client->id,
                'has_plan' => false,
                'is_locked' => false,
                'is_in_grace' => false,
                'days_until_expiry' => null,
                'expires_at' => null,
                'plan_type' => null,
            ];
        }

        $plan = $this->getActivePlan($client);

        if (! $plan) {
            // Pagó un plan mensual pero el coach aún no le asignó un AssignedPlan.
            return [
                'client_id' => $client->id,
                'has_plan' => false,
                'is_locked' => true,
                'is_in_grace' => false,
                'days_until_expiry' => null,
                'expires_at' => null,
                'plan_type' => $this->clientPlanValue($client),
                'reason' => 'awaiting_coach_assignment',
            ];
        }

        $daysUntilExpiry = $plan->daysUntilExpiry();
        $isLocked = $plan->isExpired();

        return [
            'client_id' => $client->id,
            'has_plan' => true,
            'is_locked' => $isLocked,
            'is_in_grace' => ! $isLocked && $daysUntilExpiry !== null && $daysUntilExpiry > 0 && $daysUntilExpiry <= self::GRACE_DAYS_BEFORE_EXPIRY,
            'days_until_expiry' => $daysUntilExpiry,
            'expires_at' => $plan->expires_at?->toDateString(),
            'plan_type' => $plan->plan_type,
        ];
    }

    private function clientPlanValue(Client $client): ?string
    {
        $plan = $client->plan;

        if ($plan instanceof PlanType) {
            return $plan->value;
        }

        $value = (string) ($plan ?? '');

        return $value !== '' ? $value : null;
    }
}
