<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachContractAcceptance extends Model
{
    protected $table = 'coach_contract_acceptances';

    protected $fillable = [
        'coach_id',
        'contract_version',
        'status',
        'accepted_at',
        'declined_at',
        'ip_address',
        'user_agent',
        'content_hash',
        'scroll_completed',
    ];

    protected $casts = [
        'accepted_at'      => 'datetime',
        'declined_at'      => 'datetime',
        'scroll_completed' => 'boolean',
    ];
}
