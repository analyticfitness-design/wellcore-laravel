<?php

namespace App\Enums;

enum HabitType: string
{
    case Agua = 'agua';
    case Sueno = 'sueno';
    case Entrenamiento = 'entrenamiento';
    case Nutricion = 'nutricion';
    case Suplementos = 'suplementos';
    case Estres = 'estres';
}
