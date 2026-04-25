<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'admin_id', 'assigned_at', 'source', 'coach_invitation_id', 'active'])]
class ClientCoach extends Model
{
    protected $table = 'client_coach';

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(CoachInvitation::class, 'coach_invitation_id');
    }
}
