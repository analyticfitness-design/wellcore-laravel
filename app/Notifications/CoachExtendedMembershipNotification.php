<?php

namespace App\Notifications;

use App\Models\Admin;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoachExtendedMembershipNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Client $client,
        public Admin $actor,
        public ?string $previousExpiresAt,
        public string $newExpiresAt,
        public ?string $notes,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $roleLabel = $this->actor->role?->label() ?? 'Operador';
        $previous = $this->previousExpiresAt
            ? Carbon::parse($this->previousExpiresAt)->format('d M Y')
            : 'sin fecha previa';
        $newFmt = Carbon::parse($this->newExpiresAt)->format('d M Y');

        $message = (new MailMessage)
            ->subject("[Auditoría] {$roleLabel} extendió membresía de {$this->client->name}")
            ->greeting('Auditoría WellCore')
            ->line("**{$this->actor->name}** ({$roleLabel}) extendió manualmente la membresía de un cliente.")
            ->line("**Cliente:** {$this->client->name} (#{$this->client->id})")
            ->line("**Fecha anterior:** {$previous}")
            ->line("**Nueva fecha de corte:** {$newFmt}");

        if ($this->notes) {
            $message->line("**Notas del operador:** {$this->notes}");
        }

        return $message
            ->action('Ver historial de auditoría', url('/admin/extensions'))
            ->line('Si esta extensión no fue autorizada, revisa el historial y contacta al operador inmediatamente.');
    }
}
