<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Data;

use App\Models\Kb\Methodology;
use App\Services\DecisionEngine\Data\ClientProfile;

/**
 * Input inmutable del ComposeEngine.
 *
 * Combina ClientProfile (qué quiere el cliente) + Methodology (cómo entrenar)
 * + fecha de inicio. El motor v2 nunca compone sin estos 3 piezas.
 */
final readonly class ComposeContext
{
    public function __construct(
        public ClientProfile $profile,
        public Methodology $methodology,
        public string $fechaInicio,
        public ?string $clientName = null,
        public ?string $coachName = null,
        /** @var string[] equipo disponible (gym_completo, casa_basico, casa_premium, etc.) */
        public array $equipmentAvailable = ['gym_completo'],
    ) {
    }
}
