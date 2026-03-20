<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'coach_id',
    'title',
    'video_url',
    'thumbnail_url',
    'duration_sec',
    'is_active',
    'sort_order',
])]
class CoachVideoTip extends Model
{
    protected $table = 'coach_video_tips';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
