<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'coach_id',
    'title',
    'audio_url',
    'duration_sec',
    'category',
    'plan_access',
    'sort_order',
    'is_active',
])]
class CoachAudio extends Model
{
    protected $table = 'coach_audio';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'plan_access' => 'array',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
