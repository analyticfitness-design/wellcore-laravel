<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds aggressive Cache-Control headers for hashed/static assets so browsers
 * (and any upstream CDN/NGINX) can cache them efficiently.
 *
 * - /build/assets/*  → immutable, 1 year (Vite hashes the filename)
 * - /images/*, /fonts/*, /icons/* → public, 1 week
 * - /favicon*, /apple-touch-icon* → public, 1 week
 *
 * HTML responses are left untouched so Laravel / controllers retain control
 * over page cache semantics.
 */
class SetAssetCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $path = $request->path();

        // Vite hashed assets — immutable, 1 year
        if (str_starts_with($path, 'build/assets/')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            return $response;
        }

        // Static public assets — 1 week
        $staticPrefixes = ['images/', 'fonts/', 'icons/'];
        foreach ($staticPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $response->headers->set('Cache-Control', 'public, max-age=604800');
                return $response;
            }
        }

        // Favicons / apple-touch-icon at root
        if (preg_match('#^(favicon[\w.\-]*\.(ico|png|svg)|apple-touch-icon[\w.\-]*\.png)$#i', $path)) {
            $response->headers->set('Cache-Control', 'public, max-age=604800');
            return $response;
        }

        return $response;
    }
}
