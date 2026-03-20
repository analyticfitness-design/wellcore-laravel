<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'client_id',
    'achievement_type',
    'achievement_data',
    'share_token',
    'views',
])]
class SharedAchievement extends Model
{
    protected $table = 'shared_achievements';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'achievement_data' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
