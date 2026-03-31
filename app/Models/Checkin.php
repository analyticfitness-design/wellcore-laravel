<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\Concerns\AutoCreatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'week_label',
    'checkin_date',
    'bienestar',
    'dias_entrenados',
    'nutricion',
    'comentario',
    'coach_reply',
    'replied_at',
    'rpe',
])]
class Checkin extends Model
{
    use AutoCreatedAt;

    protected $table = 'checkins';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'checkin_date' => 'date',
            'replied_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
