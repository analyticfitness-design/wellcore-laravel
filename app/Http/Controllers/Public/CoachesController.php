<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CoachesController extends Controller
{
    public function index(Request $request)
    {
        $split        = (float) config('wellcore.coach_split', 0.6);
        $planCop      = (int)   config('wellcore.coach_calc_plan_cop', 380000);

        // Calculadora ingresos — defaults para el slider.
        $calc = [
            'min'                => 1,
            'max'                => 30,
            'default'            => 10,
            'price_per_client'   => $planCop,
            'split'              => $split,
            'currency'           => 'COP',
            'locale'             => 'es-CO',
        ];

        // Ticker anonimizado de coaches activos (iniciales + país + plan).
        // VISTA DE EJEMPLO. Reemplazar por query a coaches reales cuando se confirme.
        // TODO: sustituir por App\Models\User::where('role','coach')->where('status','active') anonimizado.
        $tickerCoaches = [
            ['name' => 'C.R. · Bogotá',     'metric' => '14 CLIENTES', 'detail' => '92% RETENCIÓN · 18 MESES'],
            ['name' => 'A.M. · Medellín',   'metric' => '11 CLIENTES', 'detail' => '88% RETENCIÓN · 12 MESES'],
            ['name' => 'V.T. · Bucaramanga','metric' => '8 CLIENTES',  'detail' => '95% RETENCIÓN · 8 MESES'],
            ['name' => 'S.M. · Cali',       'metric' => '17 CLIENTES', 'detail' => '90% RETENCIÓN · 24 MESES'],
            ['name' => 'P.A. · Barranquilla','metric'=> '9 CLIENTES',  'detail' => '93% RETENCIÓN · 6 MESES'],
            ['name' => 'L.G. · Quito',      'metric' => '12 CLIENTES', 'detail' => '89% RETENCIÓN · 14 MESES'],
        ];

        // FAQ económico — preguntas frecuentes sobre el modelo de negocio.
        $faqs = [
            ['q' => __('coaches.faq.q1'), 'a' => __('coaches.faq.a1'), 'cat' => 'comisiones'],
            ['q' => __('coaches.faq.q2'), 'a' => __('coaches.faq.a2'), 'cat' => 'pagos'],
            ['q' => __('coaches.faq.q3'), 'a' => __('coaches.faq.a3'), 'cat' => 'exclusividad'],
            ['q' => __('coaches.faq.q4'), 'a' => __('coaches.faq.a4'), 'cat' => 'minimos'],
            ['q' => __('coaches.faq.q5'), 'a' => __('coaches.faq.a5'), 'cat' => 'soporte'],
            ['q' => __('coaches.faq.q6'), 'a' => __('coaches.faq.a6'), 'cat' => 'salida'],
        ];

        return view('public.coaches', [
            'calc'          => $calc,
            'tickerCoaches' => $tickerCoaches,
            'faqs'          => $faqs,
        ]);
    }
}
