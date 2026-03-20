<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'logo_url',
    'active',
])]
class ShopBrand extends Model
{
    protected $table = 'shop_brands';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(ShopProduct::class, 'brand_id');
    }
}
