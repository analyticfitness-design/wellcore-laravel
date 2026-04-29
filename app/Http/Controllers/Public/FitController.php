<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class FitController extends Controller
{
    /**
     * Render /fit con 3 planes (Esencial / Método / Íntimo) × 3 períodos
     * (mensual / trimestral / anual). Mismos descuentos que /planes:
     * trimestral -10%, anual -20%. Precios en USD (sub-brand internacional).
     *
     * TODO: validar pricing real con Silvia antes del lanzamiento. Hoy:
     *   - Esencial $130/mes
     *   - Método  $180/mes (mejor valor — referencia mantenida del v2)
     *   - Íntimo  $280/mes (top tier 1:1 con videollamada quincenal)
     */
    public function index()
    {
        $tickerItems = [
            ['name' => 'A.R. · Colombia',   'metric' => '−12 KG',      'detail' => '16 SEM · PROTOCOLO'],
            ['name' => 'M.G. · México',     'metric' => '+8% FUERZA',  'detail' => '12 SEM · PROTOCOLO'],
            ['name' => 'V.T. · Argentina',  'metric' => '−9% GRASA',   'detail' => '20 SEM · PROTOCOLO'],
            ['name' => 'L.H. · Chile',      'metric' => '94% ADHER.',  'detail' => '6 MES · PROTOCOLO'],
            ['name' => 'C.M. · Perú',       'metric' => '−15 KG',      'detail' => '24 SEM · PROTOCOLO'],
        ];

        $plans = ['esencial', 'metodo', 'intimo'];
        $periods = ['mensual', 'trimestral', 'anual'];
        $months = ['mensual' => 1, 'trimestral' => 3, 'anual' => 12];

        $monthlyUsd = [
            'esencial' => 130,
            'metodo'   => 180,
            'intimo'   => 280,
        ];

        $applyDiscount = static fn (int $monthly, string $period): int => match ($period) {
            'trimestral' => (int) round($monthly * 0.9),
            'anual'      => (int) round($monthly * 0.8),
            default      => $monthly,
        };

        $pricesUsd = [];
        $totalsUsd = [];
        $savingsUsd = [];
        foreach ($plans as $plan) {
            foreach ($periods as $period) {
                $perMonth = $applyDiscount($monthlyUsd[$plan], $period);
                $pricesUsd[$plan][$period]  = $perMonth;
                $totalsUsd[$plan][$period]  = $perMonth * $months[$period];
                $savingsUsd[$plan][$period] = ($monthlyUsd[$plan] - $perMonth) * $months[$period];
            }
        }

        return view('public.fit', [
            'tickerItems'   => $tickerItems,
            'whatsappSilvia' => config('wellcore.whatsapp_silvia', '573000000000'),
            'monthlyUsd'    => $monthlyUsd,
            'pricesUsd'     => $pricesUsd,
            'totalsUsd'     => $totalsUsd,
            'savingsUsd'    => $savingsUsd,
        ]);
    }
}
