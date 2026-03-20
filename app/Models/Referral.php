<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'referrer_id',
    'referred_email',
    'referred_id',
    'status',
    'reward_granted',
    'converted_at',
])]
class Referral extends Model
{
    protected $table = 'referrals';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'reward_granted' => 'boolean',
            'created_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'referred_id');
    }
}
