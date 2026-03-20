<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'client_id',
    'coach_id',
    'media_type',
    'media_url',
    'exercise_name',
    'notes',
    'coach_response',
    'ai_response',
    'ai_used',
    'status',
    'plan_uses_this_month',
    'responded_at',
])]
class VideoCheckin extends Model
{
    protected $table = 'video_checkins';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'ai_used' => 'boolean',
            'responded_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
