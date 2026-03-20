<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'pod_id',
    'client_id',
    'joined_at',
])]
class PodMember extends Model
{
    protected $table = 'pod_members';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function pod(): BelongsTo
    {
        return $this->belongsTo(AccountabilityPod::class, 'pod_id');
    }
}
