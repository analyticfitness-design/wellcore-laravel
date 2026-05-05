<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMention extends Model
{
    protected $table = 'post_mentions';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'comment_id',
        'mentioner_type',
        'mentioner_id',
        'mentioned_type',
        'mentioned_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function comment()
    {
        return $this->belongsTo(PostComment::class, 'comment_id');
    }

    public function scopeForUser($query, string $type, int $id)
    {
        return $query->where('mentioned_type', $type)->where('mentioned_id', $id);
    }
}
