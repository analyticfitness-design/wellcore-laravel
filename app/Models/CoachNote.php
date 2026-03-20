<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'coach_id',
    'client_id',
    'note',
    'note_type',
])]
class CoachNote extends Model
{
    protected $table = 'coach_notes';

    public $timestamps = true;

    protected function casts(): array
    {
        return [];
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
