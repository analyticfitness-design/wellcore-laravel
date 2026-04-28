<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'actor_type', 'actor_id', 'actor_name',
    'via_actor_type', 'via_actor_id', 'via_actor_name',
    'target_type', 'target_id', 'target_name',
    'target_client_id', 'target_client_name',
    'token',
    'started_at', 'ended_at',
    'ip', 'user_agent',
])]
class ImpersonationLog extends Model
{
    protected $table = 'impersonation_logs';

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
        ];
    }

    public function scopeOpenChainOf(Builder $query, string $actorType, int $actorId): Builder
    {
        return $query->where('actor_type', $actorType)
                     ->where('actor_id', $actorId)
                     ->whereNull('ended_at');
    }
}
