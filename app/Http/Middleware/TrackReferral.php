<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackReferral
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('ref') && !session()->has('referral_code')) {
            $code = $request->query('ref');
            if (is_string($code) && strlen($code) <= 30) {
                session(['referral_code' => $code]);
            }
        }

        return $next($request);
    }
}
