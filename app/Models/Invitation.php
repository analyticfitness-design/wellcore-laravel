<?php

namespace App\Models;

use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'code',
    'plan',
    'email_hint',
    'note',
    'status',
    'created_by',
    'used_by',
    'expires_at',
    'used_at',
])]
class Invitation extends Model
{
    protected $table = 'invitations';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'plan' => PlanType::class,
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'used_by');
    }
}
