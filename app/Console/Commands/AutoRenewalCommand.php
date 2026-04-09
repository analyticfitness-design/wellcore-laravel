<?php

namespace App\Console\Commands;

use App\Enums\PlanType;
use App\Enums\UserType;
use App\Mail\PlanExpiring;
use App\Models\Client;
use App\Models\Payment;
use App\Models\WellcoreNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AutoRenewalCommand extends Command
{
    protected $signature = 'wellcore:auto-renewal';

    protected $description = 'Send plan expiry reminders and flag clients needing renewal';

    public function handle(): int
    {
        $this->info('Processing renewal reminders...');

        $clients = Client::where('status', 'activo')->get();
        $reminded = 0;
        $flagged = 0;

        foreach ($clients as $client) {
            $lastPayment = Payment::where('client_id', $client->id)
                ->where('status', 'approved')
                ->latest('created_at')
                ->first();

            if (! $lastPayment) {
                continue;
            }

            $daysSincePayment = $lastPayment->created_at->diffInDays(now());

            // 3 days before 30-day cycle ends: send expiry reminder email
            if ($daysSincePayment >= 27 && $daysSincePayment < 30 && $client->email) {
                $planName = $lastPayment->plan instanceof PlanType
                    ? $lastPayment->plan->value
                    : ($lastPayment->plan ?? 'Plan WellCore');

                Mail::to($client->email)->queue(new PlanExpiring(
                    clientName: $client->name ?? 'Cliente',
                    planName: $planName,
                    expiryDate: $lastPayment->created_at->addDays(30)->format('d/m/Y'),
                    renewalAmount: number_format((float) $lastPayment->amount, 0, '.', '.'),
                ));
                $reminded++;
            }

            // 30+ days: notify admin that client needs renewal
            if ($daysSincePayment >= 30) {
                $alreadyNotified = WellcoreNotification::where('user_type', UserType::Admin)
                    ->where('user_id', 1)
                    ->where('type', 'renewal_needed')
                    ->where('body', 'like', "%#{$client->id}%")
                    ->where('created_at', '>=', now()->startOfDay())
                    ->exists();

                if (! $alreadyNotified) {
                    WellcoreNotification::create([
                        'user_type' => UserType::Admin,
                        'user_id' => 1,
                        'type' => 'renewal_needed',
                        'title' => 'Renovacion pendiente',
                        'body' => "{$client->name} (#{$client->id}) necesita renovacion — ultimo pago hace {$daysSincePayment} dias",
                    ]);
                    $flagged++;
                }
            }
        }

        $this->info("Sent {$reminded} expiry reminders, flagged {$flagged} for admin.");

        return self::SUCCESS;
    }
}
