<?php

namespace App\Services;

class PricingService
{
    private const BILLABLE_PLANS = ['esencial', 'metodo', 'elite', 'rise'];

    public function priceFor(string $plan): int
    {
        return (int) config("plans.{$plan}.price_cop", 0);
    }

    public function originalPriceFor(string $plan): int
    {
        return (int) config("plans.{$plan}.price_cop_original", $this->priceFor($plan));
    }

    public function configFor(string $plan): ?array
    {
        return config("plans.{$plan}");
    }

    /** @return array<string, int> plan -> price_cop */
    public function allPrices(): array
    {
        $prices = [];
        foreach (self::BILLABLE_PLANS as $plan) {
            $price = $this->priceFor($plan);
            if ($price > 0) {
                $prices[$plan] = $price;
            }
        }

        return $prices;
    }
}
