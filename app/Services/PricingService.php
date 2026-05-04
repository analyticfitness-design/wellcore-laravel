<?php

namespace App\Services;

use Carbon\Carbon;

class PricingService
{
    private const BILLABLE_PLANS = ['esencial', 'metodo', 'elite', 'entreno_solo', 'nutricion_solo', 'rise'];

    public function priceFor(string $plan): int
    {
        return $this->priceCop($plan);
    }

    public function priceCop(string $plan): int
    {
        $cfg = config("plans.{$plan}");
        if (! $cfg) {
            return 0;
        }

        if ($this->isPromoActive()) {
            return (int) ($cfg['price_cop'] ?? 0);
        }

        return (int) ($cfg['price_cop_original'] ?? $cfg['price_cop'] ?? 0);
    }

    public function priceUsd(string $plan): int
    {
        $cfg = config("plans.{$plan}");
        if (! $cfg) {
            return 0;
        }

        return $this->isPromoActive()
            ? (int) ($cfg['price_usd'] ?? 0)
            : (int) ($cfg['price_usd_original'] ?? $cfg['price_usd'] ?? 0);
    }

    public function isPromoActive(): bool
    {
        if (! config('plans.promo.active', false)) {
            return false;
        }

        $endsAt = config('plans.promo.ends_at');
        if (! $endsAt) {
            return true;
        }

        return Carbon::now()->lessThanOrEqualTo(Carbon::parse($endsAt)->endOfDay());
    }

    public function discountPercent(string $plan): int
    {
        if (! $this->isPromoActive()) {
            return 0;
        }

        $orig = (int) config("plans.{$plan}.price_cop_original", 0);
        $current = (int) config("plans.{$plan}.price_cop", 0);

        if ($orig <= 0 || $current >= $orig) {
            return 0;
        }

        return (int) round(($orig - $current) / $orig * 100);
    }

    public function originalPriceFor(string $plan): int
    {
        return (int) config("plans.{$plan}.price_cop_original", $this->priceCop($plan));
    }

    public function configFor(string $plan): ?array
    {
        return config("plans.{$plan}");
    }

    /** @return array<string, int> plan -> price_cop (respecting promo state) */
    public function allPrices(): array
    {
        $prices = [];
        foreach (self::BILLABLE_PLANS as $plan) {
            $price = $this->priceCop($plan);
            if ($price > 0) {
                $prices[$plan] = $price;
            }
        }

        return $prices;
    }
}
