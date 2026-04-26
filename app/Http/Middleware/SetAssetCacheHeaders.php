<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAssetCacheHeaders
{
    private const IMMUTABLE_PREFIXES = [
        'build/assets/',
        'js/',
        'fonts/',
        'images/',
        'icons/',
    ];

    private const ONE_YEAR = 31536000;

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $path = $request->path();

        foreach (self::IMMUTABLE_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $response->headers->set(
                    'Cache-Control',
                    'public, max-age='.self::ONE_YEAR.', immutable'
                );
                return $response;
            }
        }

        if (preg_match('#^(favicon[\w.\-]*\.(ico|png|svg)|apple-touch-icon[\w.\-]*\.png)$#i', $path)) {
            $response->headers->set(
                'Cache-Control',
                'public, max-age='.self::ONE_YEAR.', immutable'
            );
            return $response;
        }

        return $response;
    }
}
