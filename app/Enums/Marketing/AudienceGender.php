<?php
declare(strict_types=1);
namespace App\Enums\Marketing;

/**
 * Cases match coach_marketing_profiles.audience_gender DB enum exactly.
 * M2 will add label() and helper methods.
 */
enum AudienceGender: string
{
    case Mujeres = 'mujeres';
    case Hombres = 'hombres';
    case Mixto   = 'mixto';
}
