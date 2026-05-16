<?php

namespace App\Listeners;

use App\Events\MembershipExtendedByCoach;
use App\Notifications\CoachExtendedMembershipNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Envía email de auditoría al superadmin cuando un coach/admin
 * extiende manualmente la membresía de un cliente.
 *
 * El destinatario es configurable via wellcore.audit_email
 * (default: info@wellcorefitness.com).
 */
class NotifySuperadminOfCoachExtension implements ShouldQueue
{
    public function handle(MembershipExtendedByCoach $event): void
    {
        $auditEmail = config('wellcore.audit_email', 'info@wellcorefitness.com');

        try {
            Notification::route('mail', $auditEmail)
                ->notify(new CoachExtendedMembershipNotification(
                    client: $event->client,
                    actor: $event->actor,
                    previousExpiresAt: $event->previousExpiresAt,
                    newExpiresAt: $event->newExpiresAt,
                    notes: $event->notes,
                ));

            $event->extension->update(['notification_sent_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('Failed to send coach extension audit email', [
                'extension_id' => $event->extension->id,
                'audit_email' => $auditEmail,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
