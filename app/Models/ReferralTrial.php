<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'referral_code',
    'referrer_client_id',
    'referred_email',
    'trial_days',
    'trial_starts_at',
    'trial_expires_at',
    'converted',
])]
class ReferralTrial extends Model
{
    protected $table = 'referral_trials';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'converted' => 'boolean',
            'trial_starts_at' => 'datetime',
            'trial_expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
