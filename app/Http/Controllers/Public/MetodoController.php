<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PricingService;

class MetodoController extends Controller
{
    /**
     * Render /metodo — long-form editorial v2 (7 capítulos + sidebar editorial).
     *
     * Pasa el precio mensual del plan Esencial al view para:
     *   - JSON-LD EducationalOrganization → offers.price (schema.org).
     *   - Inline CTAs / sticky CTA con sub-precio si se necesita.
     *
     * Pattern espejo de PlanesController para mantener consistencia.
     */
    public function index(PricingService $pricing)
    {
        return view('public.metodo', [
            'monthlyEsencialCop' => $pricing->priceCop('esencial'),
            'monthlyEsencialUsd' => $pricing->priceUsd('esencial'),
        ]);
    }
}
