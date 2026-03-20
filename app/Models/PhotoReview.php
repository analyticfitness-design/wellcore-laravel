<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'photo_date',
    'review_text',
    'tokens_used',
])]
class PhotoReview extends Model
{
    protected $table = 'photo_reviews';

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
