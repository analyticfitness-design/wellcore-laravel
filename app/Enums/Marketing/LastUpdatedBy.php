<?php
declare(strict_types=1);
namespace App\Enums\Marketing;

/**
 * Cases match coach_marketing_profiles.last_updated_by DB enum exactly.
 * M2 will add label() and helper methods.
 */
enum LastUpdatedBy: string
{
    case Coach = 'coach';
    case Admin = 'admin';
}
