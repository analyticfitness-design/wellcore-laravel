<?php

namespace App\Models;

use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'id',
    'status',
    'plan',
    'nombre',
    'apellido',
    'email',
    'whatsapp',
    'ciudad',
    'pais',
    'edad',
    'objetivo',
    'experiencia',
    'lesion',
    'detalle_lesion',
    'dias_disponibles',
    'horario',
    'como_conocio',
    'ip_hash',
])]
class Inscription extends Model
{
    protected $table = 'inscriptions';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // Use tryFrom() so rows with empty/invalid plan values don't throw ValueError
    protected function plan(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ($value !== null && $value !== '') ? PlanType::tryFrom($value) : null,
        );
    }
}
