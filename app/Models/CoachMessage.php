<?php

namespace App\Models;

use App\Models\Concerns\AutoCreatedAt;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'coach_id',
    'client_id',
    'message',
    'direction',
    'read_at',
    'auto',
])]
class CoachMessage extends Model
{
    use AutoCreatedAt;

    protected $table = 'coach_messages';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'created_at' => 'datetime',
            'auto' => 'boolean',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
