<?php

namespace App\Enums;

enum PaymentProofMethod: string
{
    case Transferencia = 'transferencia';
    case Efectivo = 'efectivo';
    case Nequi = 'nequi';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Transferencia => 'Transferencia',
            self::Efectivo => 'Efectivo',
            self::Nequi => 'Nequi',
            self::Otro => 'Otro',
        };
    }
}
