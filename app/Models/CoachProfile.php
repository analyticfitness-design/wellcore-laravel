<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'admin_id',
    'slug',
    'bio',
    'city',
    'experience',
    'specializations',
    'photo_url',
    'color_primary',
    'logo_url',
    'whatsapp',
    'instagram',
    'referral_code',
    'referral_commission',
    'public_visible',
])]
class CoachProfile extends Model
{
    protected $table = 'coach_profiles';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
            'referral_commission' => 'decimal:2',
            'public_visible' => 'boolean',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
