<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'admin_id',
    'achievement_type',
    'label',
    'icon',
    'earned_at',
])]
class CoachAchievement extends Model
{
    protected $table = 'coach_achievements';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
