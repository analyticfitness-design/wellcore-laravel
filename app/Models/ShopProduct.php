<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'slug',
    'name',
    'brand_id',
    'category_id',
    'description',
    'price_cop',
    'compare_price',
    'image_url',
    'image_alt',
    'servings',
    'weight',
    'flavors',
    'tags',
    'stock',
    'stock_status',
    'featured',
    'active',
    'views',
])]
class ShopProduct extends Model
{
    protected $table = 'shop_products';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'flavors' => 'array',
            'tags' => 'array',
            'featured' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ShopBrand::class, 'brand_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ShopCategory::class, 'category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(ShopOrderItem::class, 'product_id');
    }
}
