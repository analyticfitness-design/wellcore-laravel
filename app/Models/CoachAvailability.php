<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'coach_id',
    'day_of_week',
    'time_start',
    'time_end',
    'is_active',
])]
class CoachAvailability extends Model
{
    protected $table = 'coach_availability';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
