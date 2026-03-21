<?php

namespace App\Services;

use App\Models\Coupon;

class CouponService
{
    public function validate(string $code, ?string $planSlug = null, ?float $amount = null): array
    {
        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();

        if (!$coupon) {
            return ['valid' => false, 'error' => 'Cupon no encontrado'];
        }

        if (!$coupon->isValid($planSlug, $amount)) {
            if (!$coupon->active) return ['valid' => false, 'error' => 'Cupon inactivo'];
            if ($coupon->max_uses && $coupon->times_used >= $coupon->max_uses) return ['valid' => false, 'error' => 'Cupon agotado'];
            if ($coupon->valid_until && now()->gt($coupon->valid_until)) return ['valid' => false, 'error' => 'Cupon expirado'];
            return ['valid' => false, 'error' => 'Cupon no aplicable a este plan'];
        }

        $discount = $amount ? $coupon->calculateDiscount($amount) : 0;

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'final_amount' => $amount ? $amount - $discount : null,
            'description' => $coupon->type === 'percentage'
                ? "{$coupon->value}% de descuento"
                : '$' . number_format($coupon->value, 0) . ' de descuento',
        ];
    }

    public function apply(string $code): ?Coupon
    {
        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();
        if ($coupon && $coupon->isValid()) {
            $coupon->incrementUsage();
            return $coupon;
        }
        return null;
    }
}
