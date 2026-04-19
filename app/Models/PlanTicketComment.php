<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanTicketComment extends Model
{
    protected $table = 'plan_ticket_comments';

    protected $fillable = [
        'plan_ticket_id',
        'author_type',
        'author_id',
        'author_name',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(PlanTicket::class, 'plan_ticket_id');
    }
}
