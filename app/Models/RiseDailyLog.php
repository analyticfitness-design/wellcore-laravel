<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'rise_program_id',
    'log_date',
    'workout_completed',
    'workout_notes',
    'habits_completed',
    'nutrition_adherence',
    'mood_level',
    'energy_level',
])]
class RiseDailyLog extends Model
{
    protected $table = 'rise_daily_logs';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'workout_completed' => 'boolean',
        ];
    }

    public function riseProgram(): BelongsTo
    {
        return $this->belongsTo(RiseProgram::class, 'rise_program_id');
    }
}
