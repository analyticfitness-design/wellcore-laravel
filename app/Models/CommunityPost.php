<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'client_id',
    'content',
    'post_type',
    'image_path',
    'visible',
])]
class CommunityPost extends Model
{
    protected $table = 'community_posts';

    protected function casts(): array
    {
        return [
            'visible' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class, 'post_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class, 'post_id');
    }
}
