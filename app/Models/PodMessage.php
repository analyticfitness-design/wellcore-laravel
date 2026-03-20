<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'pod_id',
    'client_id',
    'message',
])]
class PodMessage extends Model
{
    protected $table = 'pod_messages';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function pod(): BelongsTo
    {
        return $this->belongsTo(AccountabilityPod::class, 'pod_id');
    }
}
