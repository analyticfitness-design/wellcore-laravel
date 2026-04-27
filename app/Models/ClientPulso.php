<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPulso extends Model
{
    protected $table = 'client_pulsos';

    protected $fillable = [
        'client_id',
        'pulso_type',
        'media_url',
        'media_type',
        'caption',
        'workout_session_id',
        'stats_overlay',
        'expires_at',
        'is_auto_generated',
        'views_count',
    ];

    protected $casts = [
        'stats_overlay'     => 'array',
        'expires_at'        => 'datetime',
        'is_auto_generated' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ClientPulsoView::class, 'pulso_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ClientPulsoReaction::class, 'pulso_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public static function expiryForType(string $type): Carbon
    {
        return match ($type) {
            'pr'    => now()->addHours(48),
            default => now()->addHours(24),
        };
    }

    public static function ringColorForType(string $type): string
    {
        return match ($type) {
            'entrenamiento' => 'red',
            'pr'            => 'gold',
            'nutricion'     => 'green',
            'recuperacion'  => 'blue',
            'logro'         => 'purple',
            default         => 'gray',
        };
    }
}
