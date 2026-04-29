<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class FitController extends Controller
{
    public function index()
    {
        $tickerItems = [
            ['name' => 'A.R. · Colombia',   'metric' => '−12 KG',      'detail' => '16 SEM · PROTOCOLO'],
            ['name' => 'M.G. · México',     'metric' => '+8% FUERZA',  'detail' => '12 SEM · PROTOCOLO'],
            ['name' => 'V.T. · Argentina',  'metric' => '−9% GRASA',   'detail' => '20 SEM · PROTOCOLO'],
            ['name' => 'L.H. · Chile',      'metric' => '94% ADHER.',  'detail' => '6 MES · PROTOCOLO'],
            ['name' => 'C.M. · Perú',       'metric' => '−15 KG',      'detail' => '24 SEM · PROTOCOLO'],
        ];

        return view('public.fit', [
            'tickerItems'   => $tickerItems,
            'whatsappSilvia' => config('wellcore.whatsapp_silvia', '573000000000'),
        ]);
    }
}
