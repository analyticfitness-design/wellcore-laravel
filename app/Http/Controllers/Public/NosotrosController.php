<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PricingService;

class NosotrosController extends Controller
{
    /**
     * Render /nosotros — brand storytelling editorial v2.
     *
     * Estructura: Founder hero + Manifiesto editorial + Timeline 5 hitos +
     * Equipo 6 personas (1 founder con bio + 5 placeholders por iniciales)
     * + 3 valores pull-quote brutales + CTA suave hacia /metodo.
     *
     * Pasa el precio mensual del plan Esencial al view (consistente con
     * MetodoController y ProcesoController) — disponible para JSON-LD
     * Organization offers o sub-CTA si fuese necesario en futuro.
     *
     * Pattern espejo de MetodoController/ProcesoController.
     *
     * Spec: 05-nosotros/redesigned-mobile.html + prompt-implementacion-blade.md
     * Postmortem ref: docs/POSTMORTEM_PLANES_V2_500_2026-04-28.md §5,§11,§12,§13
     *      → controller dispatch obligatorio (NO closure en routes/web.php).
     */
    public function index(PricingService $pricing)
    {
        return view('public.nosotros', [
            'monthlyEsencialCop' => $pricing->priceCop('esencial'),
            'monthlyEsencialUsd' => $pricing->priceUsd('esencial'),
        ]);
    }
}
