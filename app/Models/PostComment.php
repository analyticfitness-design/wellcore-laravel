<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'post_id',
    'client_id',
    'content',
    'author_type',
    'author_admin_id',
])]
class PostComment extends Model
{
    use HasFactory;

    protected $table = 'post_comments';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeByCoach($query)
    {
        return $query->where('author_type', 'coach');
    }

    public function scopeByAdmin($query)
    {
        return $query->where('author_type', 'admin');
    }
}
