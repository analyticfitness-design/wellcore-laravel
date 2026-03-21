<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupons';

    protected $fillable = [
        'code', 'type', 'value', 'min_amount', 'max_uses',
        'times_used', 'valid_from', 'valid_until', 'applicable_plans', 'active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'active' => 'boolean',
    ];

    public function isValid(?string $planSlug = null, ?float $amount = null): bool
    {
        if (!$this->active) return false;
        if ($this->max_uses && $this->times_used >= $this->max_uses) return false;
        if ($this->valid_from && now()->lt($this->valid_from)) return false;
        if ($this->valid_until && now()->gt($this->valid_until)) return false;
        if ($amount && $this->min_amount && $amount < $this->min_amount) return false;
        if ($planSlug && $this->applicable_plans) {
            $plans = explode(',', $this->applicable_plans);
            if (!in_array($planSlug, $plans)) return false;
        }
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->type === 'percentage') {
            return round($amount * ($this->value / 100), 2);
        }
        return min($this->value, $amount);
    }

    public function incrementUsage(): void
    {
        $this->increment('times_used');
    }
}
