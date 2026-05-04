<?php

namespace App\Enums;

enum PlanType: string
{
    case Esencial = 'esencial';
    case Metodo = 'metodo';
    case Elite = 'elite';
    case EntrenoSolo = 'entreno_solo';
    case NutricionSolo = 'nutricion_solo';
    case Rise = 'rise';
    case Presencial = 'presencial';
    case Trial = 'trial';

    public function label(): string
    {
        return match ($this) {
            self::Esencial => 'Esencial',
            self::Metodo => 'Metodo',
            self::Elite => 'Elite',
            self::EntrenoSolo => 'Entreno',
            self::NutricionSolo => 'Nutrición',
            self::Rise => 'Rise',
            self::Presencial => 'Presencial',
            self::Trial => 'Trial',
        };
    }
}
