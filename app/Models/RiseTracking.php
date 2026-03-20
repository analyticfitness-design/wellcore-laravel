<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'log_date',
    'training_done',
    'nutrition_done',
    'water_liters',
    'sleep_hours',
    'note',
])]
class RiseTracking extends Model
{
    protected $table = 'rise_tracking';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'training_done' => 'boolean',
            'nutrition_done' => 'boolean',
            'water_liters' => 'decimal:1',
            'sleep_hours' => 'decimal:1',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
