<?php

namespace App\Enums;

enum PlanTicketStatus: string
{
    case Borrador = 'borrador';
    case Pendiente = 'pendiente';
    case EnRevision = 'en_revision';
    case Completado = 'completado';
    case Rechazado = 'rechazado';

    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Pendiente => 'Pendiente',
            self::EnRevision => 'En revision',
            self::Completado => 'Completado',
            self::Rechazado => 'Rechazado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Pendiente => 'yellow',
            self::EnRevision => 'blue',
            self::Completado => 'green',
            self::Rechazado => 'red',
        };
    }
}
