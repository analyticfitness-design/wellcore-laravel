<?php
declare(strict_types=1);
namespace App\Enums\Marketing;

/**
 * Cases match coach_marketing_profiles.audience_offer_main DB enum exactly.
 * M2 will add label() and helper methods.
 */
enum AudienceOfferMain: string
{
    case Esencial   = 'esencial';
    case Metodo     = 'metodo';
    case Elite      = 'elite';
    case Presencial = 'presencial';
    case Otro       = 'otro';
}
