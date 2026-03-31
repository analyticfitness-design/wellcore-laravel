<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\Concerns\AutoCreatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'photo_date',
    'tipo',
    'filename',
])]
class ProgressPhoto extends Model
{
    use AutoCreatedAt;

    protected $table = 'progress_photos';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'photo_date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
