<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'photo_id',
    'coach_id',
    'client_id',
    'note_text',
    'x_pct',
    'y_pct',
    'read_at',
])]
class ProgressPhotoNote extends Model
{
    protected $table = 'progress_photo_notes';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'x_pct' => 'float',
            'y_pct' => 'float',
            'created_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(ProgressPhoto::class, 'photo_id');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
