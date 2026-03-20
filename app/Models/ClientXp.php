<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'xp_total',
    'level',
    'streak_days',
    'streak_last_date',
    'streak_protected',
])]
class ClientXp extends Model
{
    protected $table = 'client_xp';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'streak_last_date' => 'date',
            'streak_protected' => 'boolean',
            'updated_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
