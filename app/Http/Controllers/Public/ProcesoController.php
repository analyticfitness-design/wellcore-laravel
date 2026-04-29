<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PricingService;

class ProcesoController extends Controller
{
    /**
     * Render /proceso — long-form storytelling v2 (5 steps + 5 mockup viz).
     *
     * Pasa el precio mensual del plan Esencial al view para:
     *   - JSON-LD HowTo → tool.priceSpecification (schema.org).
     *   - Inline CTAs / sticky CTA con sub-precio si se necesita.
     *
     * Pattern espejo de MetodoController para mantener consistencia.
     */
    public function index(PricingService $pricing)
    {
        return view('public.proceso', [
            'monthlyEsencialCop' => $pricing->priceCop('esencial'),
            'monthlyEsencialUsd' => $pricing->priceUsd('esencial'),
        ]);
    }
}
