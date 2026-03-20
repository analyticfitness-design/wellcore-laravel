<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'log_date',
    'peso',
    'porcentaje_musculo',
    'porcentaje_grasa',
    'notas',
])]
class Metric extends Model
{
    protected $table = 'metrics';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'peso' => 'decimal:2',
            'porcentaje_musculo' => 'decimal:2',
            'porcentaje_grasa' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
