<?php
declare(strict_types=1);
namespace App\Enums\Marketing;

/**
 * Cases match coach_marketing_profiles.specialty_primary DB enum exactly.
 * M2 will add label() and helper methods.
 */
enum SpecialtyPrimary: string
{
    case Fuerza           = 'fuerza';
    case Hipertrofia      = 'hipertrofia';
    case Recomposicion    = 'recomposicion';
    case PerdidaGrasa     = 'perdida_grasa';
    case MujeresPostparto = 'mujeres_postparto';
    case Funcional        = 'funcional';
    case Otro             = 'otro';
}
