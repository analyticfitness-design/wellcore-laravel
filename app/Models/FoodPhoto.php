<?php

namespace App\Models;

use App\Scopes\OwnedByClientScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'meal_name',
    'meal_index',
    'photo_date',
    'filename',
    'file_size',
    'coach_seen',
    'coach_seen_at',
    'coach_reaction',
    'coach_note',
    'xp_awarded',
    'ai_analysis',
])]
class FoodPhoto extends Model
{
    use HasFactory;

    protected $table = 'food_photos';

    protected static function booted(): void
    {
        static::addGlobalScope(new OwnedByClientScope);
    }

    protected function casts(): array
    {
        return [
            'photo_date'    => 'date',
            'coach_seen'    => 'boolean',
            'xp_awarded'    => 'boolean',
            'coach_seen_at' => 'datetime',
            'ai_analysis'   => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        return '/storage/'.ltrim((string) $this->filename, '/');
    }
}
