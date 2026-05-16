<?php

namespace App\Actions;

use App\Enums\ClientStatus;
use App\Enums\UserRole;
use App\Events\MembershipExtendedByCoach;
use App\Models\Admin;
use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\PlanExtension;
use App\Services\PlanLockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Extiende manualmente la membresía de un cliente desde el panel admin/coach.
 *
 * Reglas:
 *  - Setea `expires_at = $newExpiresAt` en TODOS los assigned_plans activos del cliente
 *    (consistente con ActivateRenewalAction que aplica el mismo cambio a todos).
 *    A diferencia del webhook Wompi, NO preserva días pre-pagados: la fecha custom
 *    es la fecha final exacta (decisión de producto).
 *  - Si status era inactivo/pendiente lo cambia a activo. NO toca suspendido/congelado
 *    (esos requieren acción administrativa separada para levantarse).
 *  - Solo afecta planes con active=true Y expires_at NOT NULL (rise/presencial/trial intactos).
 *  - Escribe audit row en plan_extensions.
 *  - Flushea cache (defensa en profundidad — AssignedPlan::booted ya lo hace en saved).
 *  - Dispara MembershipExtendedByCoach si actor !== superadmin.
 *
 * Transaccional — o se actualizan todos los planes o ninguno.
 */
class ExtendClientMembershipAction
{
    public function __construct(private PlanLockService $lockService) {}

    /**
     * @return array{plans_updated: int, previous_expires_at: ?string, new_expires_at: string, extension_id: int}
     */
    public function execute(
        Client $client,
        Carbon $newExpiresAt,
        Admin $actor,
        ?string $notes = null,
    ): array {
        return DB::transaction(function () use ($client, $newExpiresAt, $actor, $notes) {
            $newDateString = $newExpiresAt->copy()->startOfDay()->toDateString();

            $activePlans = AssignedPlan::query()
                ->forClient($client->id)
                ->active()
                ->whereNotNull('expires_at')
                ->get();

            $previousMax = $activePlans->isEmpty()
                ? null
                : Carbon::parse($activePlans->max('expires_at'))->toDateString();

            foreach ($activePlans as $plan) {
                $plan->update([
                    'expires_at' => $newDateString,
                ]);
            }

            $currentStatus = $client->status;
            if ($currentStatus === ClientStatus::Inactivo || $currentStatus === ClientStatus::Pendiente) {
                $client->update(['status' => ClientStatus::Activo]);
            }

            $extension = PlanExtension::create([
                'client_id' => $client->id,
                'actor_admin_id' => $actor->id,
                'actor_role' => $actor->role?->value ?? 'unknown',
                'previous_expires_at' => $previousMax,
                'new_expires_at' => $newDateString,
                'notes' => $notes,
            ]);

            $this->lockService->flushCache($client);

            $actorIsSuperadmin = $actor->role === UserRole::Superadmin;
            if (! $actorIsSuperadmin) {
                event(new MembershipExtendedByCoach(
                    client: $client,
                    actor: $actor,
                    previousExpiresAt: $previousMax,
                    newExpiresAt: $newDateString,
                    notes: $notes,
                    extension: $extension,
                ));
            }

            Log::info('Membership extended manually', [
                'client_id' => $client->id,
                'actor_admin_id' => $actor->id,
                'actor_role' => $actor->role?->value,
                'previous_expires_at' => $previousMax,
                'new_expires_at' => $newDateString,
                'plans_updated' => $activePlans->count(),
                'extension_id' => $extension->id,
            ]);

            return [
                'plans_updated' => $activePlans->count(),
                'previous_expires_at' => $previousMax,
                'new_expires_at' => $newDateString,
                'extension_id' => $extension->id,
            ];
        });
    }
}
