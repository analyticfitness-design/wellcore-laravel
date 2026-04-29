<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PresencialController extends Controller
{
    public function index(Request $request)
    {
        // Mapa SVG inline — Bucaramanga marker (lat 7.1193, lng -73.1227).
        // SVG simplificado: rectángulo Colombia + dot rojo. Sin Google Maps embed
        // para evitar third-party cookies y mejorar LCP.
        $location = [
            'city'         => 'Bucaramanga',
            'region'       => 'Santander, Colombia',
            'lat'          => 7.1193,
            'lng'          => -73.1227,
            // Coordenadas SVG (viewBox 0 0 300 360, mapa Colombia simplificado).
            'svg_x'        => 168,
            'svg_y'        => 110,
        ];

        // FAQ presencial — preguntas frecuentes específicas Bucaramanga.
        $faqs = [
            ['q' => __('presencial.faq.q1'), 'a' => __('presencial.faq.a1'), 'cat' => 'ubicacion'],
            ['q' => __('presencial.faq.q2'), 'a' => __('presencial.faq.a2'), 'cat' => 'sesion'],
            ['q' => __('presencial.faq.q3'), 'a' => __('presencial.faq.a3'), 'cat' => 'cancelacion'],
            ['q' => __('presencial.faq.q4'), 'a' => __('presencial.faq.a4'), 'cat' => 'horarios'],
            ['q' => __('presencial.faq.q5'), 'a' => __('presencial.faq.a5'), 'cat' => 'metodo'],
            ['q' => __('presencial.faq.q6'), 'a' => __('presencial.faq.a6'), 'cat' => 'pago'],
        ];

        return view('public.presencial', [
            'location' => $location,
            'faqs'     => $faqs,
        ]);
    }
}
