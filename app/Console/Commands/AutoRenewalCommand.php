<?php

namespace App\Console\Commands;

use App\Enums\ClientStatus;
use App\Enums\PlanType;
use App\Enums\UserType;
use App\Mail\PlanExpiring;
use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\WellcoreNotification;
use App\Services\PlanLockService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Envia recordatorios de vencimiento de plan y flaggea clientes que necesitan renovar.
 *
 * Cambio arquitectónico: ahora lee de assigned_plans.expires_at (fuente correcta para
 * el lock del cliente) en lugar de payments.created_at+30. Esto lo hace consistente
 * con PlanLockService::status() que el frontend consume.
 *
 * Ventanas:
 *   - Día 25 (5 días antes de expirar)  → email de warning
 *   - Día 27-29 (grace final)           → recordatorio adicional
 *   - Día 30 (hoy expira)               → email "expiró hoy" + notifica admin
 *   - Día 30+                           → admin notification (solo una vez por día)
 */
class AutoRenewalCommand extends Command
{
    protected $signature = 'wellcore:auto-renewal';

    protected $description = 'Send plan expiry reminders and flag clients needing renewal';

    public function handle(PlanLockService $lockService): int
    {
        $this->info('Processing renewal reminders...');

        $reminded = 0;
        $flagged = 0;
        $today = Carbon::now('America/Bogota')->startOfDay();

        // Clientes con plan asignado activo que expira en los próximos 5 días o ya expiró.
        // NO filtramos por plan_type aquí porque assigned_plans.plan_type describe el contenido
        // (nutricion/entrenamiento/etc), no el nivel de pago. El filtro real es el client.plan
        // mensual (esencial/metodo/elite) que aplicamos dentro del loop.
        $plans = AssignedPlan::query()
            ->active()
            ->whereNotNull('expires_at')
            ->whereNotNull('client_id')
            ->whereBetween('expires_at', [
                $today->copy()->subDays(7)->toDateString(), // ya expiró hace ≤7 días
                $today->copy()->addDays(5)->toDateString(), // expira en ≤5 días
            ])
            ->with('client')
            ->get();

        $monthlyPlans = ['esencial', 'metodo', 'elite'];

        // Deduplicar por cliente: un cliente puede tener varios planes (entrenamiento/nutricion/habitos/supl)
        // pero solo queremos enviar 1 email y crear 1 notificación por cliente por día.
        // Nos quedamos con el plan más reciente (expires_at más lejano) por cliente.
        $plansByClient = [];
        foreach ($plans as $plan) {
            $existing = $plansByClient[$plan->client_id] ?? null;
            if (! $existing || Carbon::parse($plan->expires_at)->greaterThan(Carbon::parse($existing->expires_at))) {
                $plansByClient[$plan->client_id] = $plan;
            }
        }

        foreach ($plansByClient as $plan) {
            $client = $plan->client;

            if (! $client || $client->status !== ClientStatus::Activo) {
                continue;
            }

            $clientPlan = $client->plan instanceof PlanType
                ? $client->plan->value
                : (string) ($client->plan ?? '');

            // Solo lockeamos planes mensuales — los demás (rise/presencial/trial) siguen otro flujo
            if (! in_array($clientPlan, $monthlyPlans, true)) {
                continue;
            }

            $expiresAt = Carbon::parse($plan->expires_at)->startOfDay();
            $daysUntilExpiry = (int) $today->diffInDays($expiresAt, false);

            // Día 25-29 (≤5 días, aún no expiró): enviar email si no se envió hoy
            if ($daysUntilExpiry > 0 && $daysUntilExpiry <= 5 && $client->email) {
                $sentToday = WellcoreNotification::where('user_type', UserType::Client)
                    ->where('user_id', $client->id)
                    ->where('type', 'renewal_reminder')
                    ->whereDate('created_at', $today)
                    ->exists();

                if (! $sentToday) {
                    $this->sendReminderEmail($client, $clientPlan, $expiresAt);
                    WellcoreNotification::create([
                        'user_type' => UserType::Client,
                        'user_id' => $client->id,
                        'type' => 'renewal_reminder',
                        'title' => 'Tu plan expira pronto',
                        'body' => "Tu plan {$clientPlan} expira en {$daysUntilExpiry} días.",
                    ]);
                    $reminded++;
                }
            }

            // Día 30 (expiró hoy o ya pasó): notifica admin una sola vez por día
            if ($daysUntilExpiry <= 0) {
                $alreadyNotified = WellcoreNotification::where('user_type', UserType::Admin)
                    ->where('user_id', 1)
                    ->where('type', 'renewal_needed')
                    ->where('body', 'like', "%#{$client->id}%")
                    ->whereDate('created_at', $today)
                    ->exists();

                if (! $alreadyNotified) {
                    $daysPast = abs($daysUntilExpiry);
                    WellcoreNotification::create([
                        'user_type' => UserType::Admin,
                        'user_id' => 1,
                        'type' => 'renewal_needed',
                        'title' => 'Renovacion pendiente',
                        'body' => "{$client->name} (#{$client->id}) — plan {$clientPlan} expiró hace {$daysPast} días",
                    ]);
                    $flagged++;
                }

                // Invalida cache para que el próximo request del cliente vea el lock
                $lockService->flushCache($client);
            }
        }

        $this->info("Sent {$reminded} expiry reminders, flagged {$flagged} for admin.");

        return self::SUCCESS;
    }

    private function sendReminderEmail(Client $client, string $planName, Carbon $expiresAt): void
    {
        // planName viene ya normalizado desde el caller (esencial|metodo|elite)
        $renewalAmount = app(PricingService::class)->priceFor($planName);

        try {
            Mail::to($client->email)->queue(new PlanExpiring(
                clientName: $client->name ?? 'Cliente',
                planName: $planName,
                expiryDate: $expiresAt->format('d/m/Y'),
                renewalAmount: number_format($renewalAmount, 0, '.', '.'),
            ));
        } catch (\Throwable $e) {
            \Log::error('AutoRenewal mail failed', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
