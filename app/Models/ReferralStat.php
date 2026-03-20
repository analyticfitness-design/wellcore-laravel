<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'coach_id',
    'visitor_hash',
    'source_url',
    'converted',
    'conversion_id',
])]
class ReferralStat extends Model
{
    protected $table = 'referral_stats';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'converted' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
