<?php

namespace App\Enums;

enum PaymentProofStatus: string
{
    case Pendiente = 'pendiente';
    case Aprobado = 'aprobado';
    case Rechazado = 'rechazado';
    case Expirado = 'expirado';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Aprobado => 'Aprobado',
            self::Rechazado => 'Rechazado',
            self::Expirado => 'Expirado',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pendiente => 'bg-yellow-500/15 text-yellow-300',
            self::Aprobado => 'bg-green-500/15 text-green-400',
            self::Rechazado => 'bg-red-600/15 text-red-400',
            self::Expirado => 'bg-gray-500/15 text-gray-400',
        };
    }
}
