<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PinnedPost extends Model
{
    use HasFactory;

    protected $table = 'pinned_posts';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'pinned_by_type',
        'pinned_by_id',
        'pinned_at',
        'pinned_until',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'pinned_at' => 'datetime',
            'pinned_until' => 'datetime',
        ];
    }

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('pinned_until')->orWhere('pinned_until', '>', now());
        });
    }
}
