<?php

namespace App\Models;

use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
            'plan' => PlanType::class,
            'created_at' => 'datetime',
        ];
    }
}
