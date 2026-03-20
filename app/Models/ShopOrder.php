<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_code',
    'client_id',
    'guest_name',
    'guest_email',
    'guest_phone',
    'guest_city',
    'guest_address',
    'guest_notes',
    'subtotal_cop',
    'shipping_cop',
    'total_cop',
    'status',
    'payment_method',
    'payment_ref',
    'tracking_code',
])]
class ShopOrder extends Model
{
    protected $table = 'shop_orders';

    public $timestamps = true;

    protected function casts(): array
    {
        return [];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShopOrderItem::class, 'order_id');
    }
}
