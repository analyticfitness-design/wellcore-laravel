<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Admin = 'admin';
    case Coach = 'coach';
    case Jefe = 'jefe';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Super Administrador',
            self::Admin => 'Administrador',
            self::Coach => 'Coach',
            self::Jefe => 'Jefe',
        };
    }
}
