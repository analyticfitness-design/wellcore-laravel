<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackUtmParameters
{
    /**
     * UTM parameter keys we track.
     */
    private const UTM_PARAMS = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
    ];

    /**
     * Cookie name for the persistent visitor identifier.
     */
    private const VISITOR_COOKIE = 'wc_visitor_id';

    /**
     * Cookie lifetime in minutes (90 days).
     */
    private const COOKIE_LIFETIME = 60 * 24 * 90;

    public function handle(Request $request, Closure $next): Response
    {
        $utmParams = $this->extractUtmParams($request);
        $hasUtmParams = count($utmParams) > 0;
        $visitorId = $request->cookie(self::VISITOR_COOKIE);
        $isFirstVisit = $visitorId === null;

        // Generate visitor ID if this is the first visit
        if ($isFirstVisit) {
            $visitorId = Str::uuid()->toString();
        }

        if ($hasUtmParams || $isFirstVisit) {
            // Store/update UTM data in session (new UTMs override old)
            if ($hasUtmParams) {
                session(['utm_data' => $utmParams]);
            }

            // Create a page visit record
            $this->recordPageVisit($request, $visitorId, $utmParams);
        }

        $response = $next($request);

        // Attach visitor cookie to response if not already set
        if ($isFirstVisit) {
            $response->headers->setCookie(cookie(
                name: self::VISITOR_COOKIE,
                value: $visitorId,
                minutes: self::COOKIE_LIFETIME,
                path: '/',
                secure: $request->isSecure(),
                httpOnly: true,
                sameSite: 'Lax',
            ));
        }

        return $response;
    }

    /**
     * Extract UTM parameters from the request query string.
     *
     * @return array<string, string>
     */
    private function extractUtmParams(Request $request): array
    {
        $params = [];

        foreach (self::UTM_PARAMS as $key) {
            $value = $request->query($key);
            if (is_string($value) && $value !== '') {
                $params[$key] = Str::limit($value, 255, '');
            }
        }

        return $params;
    }

    /**
     * Create a page_visits record in the database.
     */
    private function recordPageVisit(Request $request, string $visitorId, array $utmParams): void
    {
        try {
            PageVisit::create([
                'session_id' => session()->getId(),
                'visitor_id' => $visitorId,
                'utm_source' => $utmParams['utm_source'] ?? null,
                'utm_medium' => $utmParams['utm_medium'] ?? null,
                'utm_campaign' => $utmParams['utm_campaign'] ?? null,
                'utm_content' => $utmParams['utm_content'] ?? null,
                'utm_term' => $utmParams['utm_term'] ?? null,
                'landing_page' => Str::limit($request->fullUrl(), 2048, ''),
                'referrer' => Str::limit($request->header('referer', ''), 2048, '') ?: null,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit($request->userAgent() ?? '', 512, ''),
                'device_type' => $this->detectDeviceType($request->userAgent() ?? ''),
                'country' => null,
            ]);
        } catch (\Throwable $e) {
            // Never break the user flow for analytics tracking
            report($e);
        }
    }

    /**
     * Simple device type detection from User-Agent string.
     */
    private function detectDeviceType(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'mobile';
        }

        return 'desktop';
    }
}
