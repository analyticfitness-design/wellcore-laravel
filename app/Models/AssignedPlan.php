<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'plan_type',
    'content',
    'version',
    'assigned_by',
    'valid_from',
    'active',
])]
class AssignedPlan extends Model
{
    protected $table = 'assigned_plans';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'valid_from' => 'date',
            'active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }
}
