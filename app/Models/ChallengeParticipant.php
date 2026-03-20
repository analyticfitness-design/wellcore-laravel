<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'challenge_id',
    'client_id',
    'progress',
    'completed',
    'completed_at',
    'joined_at',
    'rank',
])]
class ChallengeParticipant extends Model
{
    protected $table = 'challenge_participants';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'progress' => 'decimal:2',
            'completed' => 'boolean',
            'completed_at' => 'datetime',
            'joined_at' => 'datetime',
        ];
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
