<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot between clients and medals.
 *
 * Extended beyond a plain pivot because it carries progress + achieved_at
 * that we query directly (not always through the relation).
 */
class ClientMedal extends Model
{
    protected $table = 'client_medals';

    protected $fillable = [
        'client_id',
        'medal_id',
        'current_progress',
        'achieved_at',
    ];

    protected $casts = [
        'current_progress' => 'integer',
        'achieved_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function medal(): BelongsTo
    {
        return $this->belongsTo(Medal::class);
    }
}
