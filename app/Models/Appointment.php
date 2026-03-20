<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'coach_id',
    'client_id',
    'scheduled_at',
    'duration_min',
    'title',
    'notes',
    'meet_link',
    'status',
])]
class Appointment extends Model
{
    protected $table = 'appointments';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'status' => AppointmentStatus::class,
        ];
    }
}
