<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'actor_type',
    'actor_id',
    'actor_name',
    'target_client_id',
    'target_client_name',
    'token',
    'started_at',
    'ended_at',
    'ip',
    'user_agent',
])]
class ImpersonationLog extends Model
{
    protected $table = 'impersonation_logs';

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }
}
