<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachRating extends Model
{
    protected $table = 'coach_ratings';

    protected $fillable = ['client_id', 'coach_id', 'rating', 'comment'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
