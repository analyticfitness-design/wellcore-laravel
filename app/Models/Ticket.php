<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'id',
    'coach_id',
    'coach_name',
    'client_name',
    'client_plan',
    'ticket_type',
    'description',
    'priority',
    'status',
    'response',
    'assigned_to',
    'deadline',
    'resolved_at',
    'ai_draft',
    'ai_status',
    'ai_generation_id',
])]
class Ticket extends Model
{
    protected $table = 'tickets';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
            'status' => TicketStatus::class,
            'deadline' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }
}
