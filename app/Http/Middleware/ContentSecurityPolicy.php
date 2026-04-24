<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        // Generate a cryptographically random nonce for this request.
        // The nonce is stored on the request attributes so layouts and
        // the @cspNonce Blade directive can access it without a global.
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

        $response = $next($request);

        if (method_exists($response, 'header')) {
            // Allow Vite dev server in local environment
            $viteDev = app()->environment('local') ? ' http://localhost:5173 ws://localhost:5173' : '';

            // Including 'nonce-{nonce}' alongside 'unsafe-inline':
            // Modern browsers that understand nonces will IGNORE 'unsafe-inline'
            // and require the nonce, giving us real script protection.
            // Older browsers fall back to 'unsafe-inline' — nothing breaks.
            // 'unsafe-eval' must remain for Alpine.js.
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' 'nonce-{$nonce}' https://fonts.googleapis.com https://www.googletagmanager.com https://www.google-analytics.com https://connect.facebook.net https://checkout.wompi.co" . $viteDev,
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com" . $viteDev,
                "font-src 'self' https://fonts.gstatic.com",
                "img-src 'self' data: https: blob:",
                "connect-src 'self' https://api.anthropic.com https://sandbox.wompi.co https://production.wompi.co https://checkout.wompi.co https://www.google-analytics.com https://www.facebook.com https://connect.facebook.net wss:" . $viteDev,
                "frame-src 'self' https://checkout.wompi.co https://www.youtube.com https://youtube.com https://www.youtube-nocookie.com",
                "base-uri 'self'",
                "form-action 'self'",
            ]);

            $response->header('Content-Security-Policy', $csp);
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-Frame-Options', 'SAMEORIGIN');
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->header('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
            $response->header('X-XSS-Protection', '1; mode=block');

            if (app()->environment('production')) {
                $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
            }
        }

        return $response;
    }
}
