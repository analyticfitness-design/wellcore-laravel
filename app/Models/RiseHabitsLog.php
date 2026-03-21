<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'rise_program_id',
    'client_id',
    'log_date',
    'water_liters',
    'sleep_hours',
    'steps',
    'meditation',
    'training_completed',
    'nutrition_followed',
    'notes',
])]
class RiseHabitsLog extends Model
{
    protected $table = 'rise_habits_logs';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'water_liters' => 'decimal:1',
            'sleep_hours' => 'decimal:1',
            'steps' => 'integer',
            'meditation' => 'boolean',
            'training_completed' => 'boolean',
            'nutrition_followed' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function riseProgram(): BelongsTo
    {
        return $this->belongsTo(RiseProgram::class);
    }
}
