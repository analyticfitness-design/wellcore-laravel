<?php

declare(strict_types=1);

use App\Enums\Marketing\AudienceAgeRange;
use App\Enums\Marketing\AudienceGender;
use App\Enums\Marketing\AudienceOfferMain;
use App\Enums\Marketing\DropStatus;
use App\Enums\Marketing\LastUpdatedBy;
use App\Enums\Marketing\PieceState;
use App\Enums\Marketing\PieceType;
use App\Enums\Marketing\SpecialtyPrimary;

it('all intake enums load with correct values', function () {
    expect(SpecialtyPrimary::Fuerza->value)->toBe('fuerza')
        ->and(AudienceAgeRange::Age25to35->value)->toBe('25-35')
        ->and(AudienceGender::Mujeres->value)->toBe('mujeres')
        ->and(AudienceOfferMain::Metodo->value)->toBe('metodo')
        ->and(LastUpdatedBy::Coach->value)->toBe('coach');
});

it('PieceType has 3 cases', fn () => expect(count(PieceType::cases()))->toBe(3));
it('PieceState has 4 cases', fn () => expect(count(PieceState::cases()))->toBe(4));
it('DropStatus has 8 cases', fn () => expect(count(DropStatus::cases()))->toBe(8));
