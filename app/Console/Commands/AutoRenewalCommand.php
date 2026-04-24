<?php

namespace App\Console\Commands;

use App\Enums\PlanType;
use App\Enums\UserType;
use App\Mail\PlanExpiring;
use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\WellcoreNotification;
use App\Services\PlanLockService;
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
        $today = Carbon::now()->startOfDay();

        // Clientes con un plan activo que expira en los próximos 5 días o ya expiró
        $plans = AssignedPlan::query()
            ->active()
            ->whereNotNull('expires_at')
            ->whereNotNull('client_id')
            ->whereIn('plan_type', ['esencial', 'metodo', 'elite'])
            ->whereBetween('expires_at', [
                $today->copy()->subDays(7)->toDateString(), // ya expiró hace ≤7 días
                $today->copy()->addDays(5)->toDateString(), // expira en ≤5 días
            ])
            ->with('client')
            ->get();

        foreach ($plans as $plan) {
            $client = $plan->client;

            if (! $client || $client->status !== \App\Enums\ClientStatus::Activo) {
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
                    $this->sendReminderEmail($client, $plan, $expiresAt);
                    WellcoreNotification::create([
                        'user_type' => UserType::Client,
                        'user_id' => $client->id,
                        'type' => 'renewal_reminder',
                        'title' => 'Tu plan expira pronto',
                        'body' => "Tu plan {$plan->plan_type} expira en {$daysUntilExpiry} días.",
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
                        'body' => "{$client->name} (#{$client->id}) — plan {$plan->plan_type} expiró hace {$daysPast} días",
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

    private function sendReminderEmail(Client $client, AssignedPlan $plan, Carbon $expiresAt): void
    {
        $planName = $plan->plan_type instanceof PlanType
            ? $plan->plan_type->value
            : (string) $plan->plan_type;

        $renewalAmount = (int) config("plans.{$planName}.price_cop", 0);

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
