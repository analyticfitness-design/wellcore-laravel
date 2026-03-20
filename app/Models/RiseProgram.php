<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'client_id',
    'enrollment_date',
    'start_date',
    'end_date',
    'experience_level',
    'training_location',
    'gender',
    'status',
    'personalized_program',
])]
class RiseProgram extends Model
{
    protected $table = 'rise_programs';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'enrollment_date' => 'datetime',
            'start_date' => 'date',
            'end_date' => 'date',
            'personalized_program' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(RiseDailyLog::class, 'rise_program_id');
    }
}
