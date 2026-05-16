<?php

namespace App\Events;

use App\Models\Admin;
use App\Models\Client;
use App\Models\PlanExtension;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disparado cuando un actor NO superadmin (coach, admin, jefe) extiende
 * manualmente la membresía de un cliente. El superadmin se autoexcluye
 * para no recibir notificaciones de sus propias acciones.
 */
class MembershipExtendedByCoach
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Client $client,
        public Admin $actor,
        public ?string $previousExpiresAt,
        public string $newExpiresAt,
        public ?string $notes,
        public PlanExtension $extension,
    ) {}
}
