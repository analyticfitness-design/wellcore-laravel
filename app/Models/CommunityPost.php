<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'client_id',
    'coach_admin_id',
    'content',
    'post_type',
    'image_path',
    'visible',
    'author_type',
    'author_admin_id',
    'is_official',
    'is_global',
])]
class CommunityPost extends Model
{
    protected $table = 'community_posts';

    protected function casts(): array
    {
        return [
            'visible'     => 'boolean',
            'is_official' => 'boolean',
            'is_global'   => 'boolean',
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

    public function pinned(): HasOne
    {
        return $this->hasOne(PinnedPost::class, 'post_id')
            ->where(function ($q) {
                $q->whereNull('pinned_until')->orWhere('pinned_until', '>', now());
            });
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PostReport::class, 'post_id');
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(PostMention::class, 'post_id');
    }

    public function scopeOfficial($query)
    {
        return $query->where('is_official', true);
    }

    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }
}
