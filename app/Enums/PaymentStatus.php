<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';
    case Voided = 'voided';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Approved => 'Aprobado',
            self::Declined => 'Rechazado',
            self::Voided => 'Anulado',
            self::Error => 'Error',
        };
    }
}
