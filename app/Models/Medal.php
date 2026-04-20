<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medal extends Model
{
    protected $table = 'medals';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'requirement',
        'target_value',
        'xp',
        'category',
        'tier',
        'icon_label',
        'stripe_color_1',
        'stripe_color_2',
        'stripe_color_3',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'target_value' => 'integer',
        'xp' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_medals')
            ->withPivot(['current_progress', 'achieved_at'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    public function getStripeColorsAttribute(): array
    {
        return [
            $this->stripe_color_1,
            $this->stripe_color_2,
            $this->stripe_color_3,
        ];
    }
}
