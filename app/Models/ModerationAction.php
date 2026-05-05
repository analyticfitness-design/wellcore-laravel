<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationAction extends Model
{
    protected $table = 'moderation_actions';

    public $timestamps = false;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'action_type',
        'target_type',
        'target_id',
        'reason',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor()
    {
        return $this->belongsTo(Admin::class, 'actor_id');
    }

    public function scopeByActor($query, string $actorType, int $actorId)
    {
        return $query->where('actor_type', $actorType)->where('actor_id', $actorId);
    }

    public function scopeForTarget($query, string $targetType, int $targetId)
    {
        return $query->where('target_type', $targetType)->where('target_id', $targetId);
    }
}
