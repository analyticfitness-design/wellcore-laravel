<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PricingService;

class PlanesController extends Controller
{
    /**
     * Render /planes con pricing dinámico (3 planes × 3 períodos × 2 monedas).
     *
     * Source of truth = PricingService::priceCop() / priceUsd().
     * Los descuentos por período (-10% trim / -20% anual) se aplican aquí —
     * NO en PricingService — para evitar afectar otras vistas que solo usan
     * el precio mensual (admin, cliente, schema.org).
     *
     * Si el modelo de descuentos cambia (ej. -15% trim), basta tocar este
     * controller. Si los precios mensuales cambian, basta tocar config/plans.php.
     */
    public function index(PricingService $pricing)
    {
        $plans = ['esencial', 'metodo', 'elite'];
        $periods = ['mensual', 'trimestral', 'anual'];
        $months = ['mensual' => 1, 'trimestral' => 3, 'anual' => 12];

        $applyDiscount = static function (int $monthly, string $period): int {
            return match ($period) {
                'trimestral' => (int) round($monthly * 0.9),
                'anual'      => (int) round($monthly * 0.8),
                default      => $monthly,
            };
        };

        $monthlyCop = [];
        $monthlyUsd = [];
        foreach ($plans as $plan) {
            $monthlyCop[$plan] = $pricing->priceCop($plan);
            $monthlyUsd[$plan] = $pricing->priceUsd($plan);
        }

        $build = static function (array $monthlyByPlan) use ($plans, $periods, $months, $applyDiscount): array {
            $prices = [];
            $totals = [];
            $savings = [];
            foreach ($plans as $plan) {
                foreach ($periods as $period) {
                    $perMonth = $applyDiscount($monthlyByPlan[$plan], $period);
                    $prices[$plan][$period]  = $perMonth;
                    $totals[$plan][$period]  = $perMonth * $months[$period];
                    $savings[$plan][$period] = ($monthlyByPlan[$plan] - $perMonth) * $months[$period];
                }
            }
            return compact('prices', 'totals', 'savings');
        };

        $cop = $build($monthlyCop);
        $usd = $build($monthlyUsd);

        return view('public.planes', [
            'monthlyCop' => $monthlyCop,
            'monthlyUsd' => $monthlyUsd,
            'pricesCop'  => $cop['prices'],
            'totalsCop'  => $cop['totals'],
            'savingsCop' => $cop['savings'],
            'pricesUsd'  => $usd['prices'],
            'totalsUsd'  => $usd['totals'],
            'savingsUsd' => $usd['savings'],
            'promoActive'   => $pricing->isPromoActive(),
            'discountPct'   => (int) config('plans.promo.discount_pct', 0),
            'promoLabel'    => (string) config('plans.promo.label', ''),
        ]);
    }
}
