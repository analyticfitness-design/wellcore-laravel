<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'edad',
    'peso',
    'altura',
    'genero',
    'objetivo',
    'ciudad',
    'whatsapp',
    'nivel',
    'lugar_entreno',
    'dias_disponibles',
    'restricciones',
    'macros',
    'intake_data',
    'rise_start_date',
    'rise_gender',
    'rise_coach',
    'bio',
    'avatar_url',
    'dashboard_video_url',
])]
class ClientProfile extends Model
{
    protected $table = 'client_profiles';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'peso' => 'decimal:1',
            'altura' => 'decimal:1',
            'dias_disponibles' => 'array',
            'macros' => 'array',
            'rise_start_date' => 'date',
            'updated_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
