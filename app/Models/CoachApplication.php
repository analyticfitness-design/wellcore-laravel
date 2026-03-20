<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'id',
    'status',
    'name',
    'email',
    'whatsapp',
    'city',
    'bio',
    'experience',
    'plan',
    'current_clients',
    'specializations',
    'referral',
    'ip_hash',
    'admin_notes',
])]
class CoachApplication extends Model
{
    protected $table = 'coach_applications';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
        ];
    }
}
