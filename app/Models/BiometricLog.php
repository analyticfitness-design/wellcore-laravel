<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'log_date',
    'steps',
    'sleep_hours',
    'heart_rate',
    'calories',
    'source',
    'weight_kg',
    'body_fat_pct',
    'waist_cm',
    'hip_cm',
    'energy_level',
    'notes',
])]
class BiometricLog extends Model
{
    protected $table = 'biometric_logs';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'sleep_hours' => 'decimal:1',
            'weight_kg' => 'decimal:2',
            'body_fat_pct' => 'decimal:1',
            'waist_cm' => 'decimal:1',
            'hip_cm' => 'decimal:1',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
