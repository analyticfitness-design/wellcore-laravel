<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'email',
    'payu_reference',
    'payu_transaction_id',
    'plan',
    'amount',
    'currency',
    'status',
    'buyer_name',
    'buyer_phone',
    'payu_response',
    'wompi_reference',
    'wompi_transaction_id',
    'payment_method',
])]
class Payment extends Model
{
    protected $table = 'payments';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'plan' => PlanType::class,
            'status' => PaymentStatus::class,
            'payu_response' => 'array',
            'amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
