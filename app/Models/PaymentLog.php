<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'event',
    'reference',
    'transaction_id',
    'payment_id',
    'status',
    'payload',
])]
class PaymentLog extends Model
{
    protected $table = 'payment_logs';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
