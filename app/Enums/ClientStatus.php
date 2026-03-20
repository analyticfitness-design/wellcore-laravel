<?php

namespace App\Enums;

enum ClientStatus: string
{
    case Activo = 'activo';
    case Inactivo = 'inactivo';
    case Suspendido = 'suspendido';
    case Pendiente = 'pendiente';
    case Congelado = 'congelado';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Inactivo => 'Inactivo',
            self::Suspendido => 'Suspendido',
            self::Pendiente => 'Pendiente',
            self::Congelado => 'Congelado',
        };
    }
}
