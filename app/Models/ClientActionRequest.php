<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'coach_id',
    'coach_name',
    'client_id',
    'client_name',
    'action',
    'reason',
    'status',
    'admin_notas',
    'resolved_by',
    'resolved_at',
])]
class ClientActionRequest extends Model
{
    protected $table = 'client_action_requests';

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('status', 'pendiente');
    }
}
