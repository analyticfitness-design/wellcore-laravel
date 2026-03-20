<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'log_date',
    'weight_kg',
    'chest_cm',
    'waist_cm',
    'hips_cm',
    'thigh_cm',
    'arm_cm',
    'muscle_pct',
    'fat_pct',
    'notes',
])]
class RiseMeasurement extends Model
{
    protected $table = 'rise_measurements';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'weight_kg' => 'decimal:2',
            'chest_cm' => 'decimal:1',
            'waist_cm' => 'decimal:1',
            'hips_cm' => 'decimal:1',
            'thigh_cm' => 'decimal:1',
            'arm_cm' => 'decimal:1',
            'muscle_pct' => 'decimal:1',
            'fat_pct' => 'decimal:1',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
