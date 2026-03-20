<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'amount_cents',
    'currency',
    'reference',
    'wompi_transaction_id',
    'status',
    'attempt_at',
    'resolved_at',
    'payment_method_id',
    'error_message',
])]
class AutoChargeLog extends Model
{
    protected $table = 'auto_charge_log';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'attempt_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
