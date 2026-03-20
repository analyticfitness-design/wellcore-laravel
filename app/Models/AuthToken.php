<?php

namespace App\Models;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_type',
    'user_id',
    'token',
    'fingerprint',
    'ip_address',
    'expires_at',
])]
class AuthToken extends Model
{
    protected $table = 'auth_tokens';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'user_type' => UserType::class,
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): Admin|Client|null
    {
        return match ($this->user_type) {
            UserType::Admin => $this->belongsTo(Admin::class, 'user_id')->first(),
            UserType::Client => $this->belongsTo(Client::class, 'user_id')->first(),
            default => null,
        };
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
