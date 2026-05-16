<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'actor_admin_id',
    'actor_role',
    'previous_expires_at',
    'new_expires_at',
    'notes',
    'notification_sent_at',
])]
class PlanExtension extends Model
{
    use HasFactory;

    protected $table = 'plan_extensions';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'previous_expires_at' => 'date',
            'new_expires_at' => 'date',
            'notification_sent_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'actor_admin_id');
    }
}
