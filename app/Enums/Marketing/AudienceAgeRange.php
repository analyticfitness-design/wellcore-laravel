<?php
declare(strict_types=1);
namespace App\Enums\Marketing;

/**
 * Cases match coach_marketing_profiles.audience_age_range DB enum exactly.
 * M2 will add label() and helper methods.
 */
enum AudienceAgeRange: string
{
    case Age18to25 = '18-25';
    case Age25to35 = '25-35';
    case Age35to45 = '35-45';
    case Age45plus = '45+';
}
