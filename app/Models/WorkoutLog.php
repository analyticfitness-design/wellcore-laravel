<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutLog extends Model
{
    protected $table = 'workout_logs';

    // vanilla PHP schema has created_at but no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'session_id',
        'exercise_name',
        'block_type',
        'block_order',
        'set_number',
        'weight_kg',
        'reps',
        'target_reps',
        'target_weight',
        'completed',
        'is_pr',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'target_weight' => 'decimal:2',
        'completed' => 'boolean',
        'is_pr' => 'boolean',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class, 'session_id');
    }

    public function volume(): float
    {
        return ($this->weight_kg ?? 0) * ($this->reps ?? 0);
    }
}
