<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id',
    'client_id',
    'exercise',
    'weight_kg',
    'sets',
    'reps',
    'rpe',
    'notes',
    'week_number',
    'year',
    'date',
])]
class WeightLog extends Model
{
    protected $table = 'weight_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'rpe' => 'decimal:1',
            'date' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
