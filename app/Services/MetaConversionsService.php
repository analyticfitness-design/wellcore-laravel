<?php

namespace App\Services;

use App\Models\Inscription;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MetaConversionsService
{
    private readonly string $pixelId;

    private readonly string $accessToken;

    private readonly ?string $testEventCode;

    private readonly string $apiVersion;

    public function __construct()
    {
        $this->pixelId = config('services.meta.pixel_id', '');
        $this->accessToken = config('services.meta.access_token', '');
        $this->testEventCode = config('services.meta.test_event_code') ?: null;
        $this->apiVersion = config('services.meta.api_version', 'v21.0');
    }

    /**
     * Check if Meta CAPI is properly configured.
     */
    public static function isConfigured(): bool
    {
        return filled(config('services.meta.pixel_id'))
            && filled(config('services.meta.access_token'));
    }

    /**
     * Send a server-side event to Meta Conversions API.
     *
     * @param  string  $eventName  Standard Meta event: PageView, Lead, Purchase, ViewContent, etc.
     * @param  array<string, string>  $userData  User identifiers (em, ph, fn, ln, ct, country).
     * @param  array<string, mixed>  $customData  Event-specific data (value, currency, content_name, etc.).
     * @param  string|null  $eventId  For deduplication with the browser pixel.
     */
    public function sendEvent(
        string $eventName,
        array $userData = [],
        array $customData = [],
        ?string $eventId = null,
    ): bool {
        if (! self::isConfigured()) {
            Log::debug('MetaConversionsService: skipping event — not configured.', [
                'event' => $eventName,
            ]);

            return false;
        }

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->pixelId}/events";

        $hashedUserData = $this->hashUserData($userData);

        // Merge platform-level identifiers
        $hashedUserData['client_ip_address'] = request()->ip();
        $hashedUserData['client_user_agent'] = request()->userAgent();

        $fbc = request()->cookie('_fbc');
        if ($fbc) {
            $hashedUserData['fbc'] = $fbc;
        }

        $fbp = request()->cookie('_fbp');
        if ($fbp) {
            $hashedUserData['fbp'] = $fbp;
        }

        $payload = [
            'data' => [[
                'event_name' => $eventName,
                'event_time' => time(),
                'event_id' => $eventId ?? Str::uuid()->toString(),
                'event_source_url' => request()->url(),
                'action_source' => 'website',
                'user_data' => $hashedUserData,
                'custom_data' => $customData ?: (object) [], // Meta expects an object, not an empty array
            ]],
        ];

        // Include test event code when configured (for testing in Meta Events Manager)
        if ($this->testEventCode) {
            $payload['test_event_code'] = $this->testEventCode;
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(5)
                ->post($url, $payload);

            if ($response->failed()) {
                Log::warning('MetaConversionsService: API returned error.', [
                    'event' => $eventName,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return false;
            }

            Log::debug('MetaConversionsService: event sent.', [
                'event' => $eventName,
                'event_id' => $payload['data'][0]['event_id'],
            ]);

            return true;
        } catch (\Throwable $e) {
            // CAPI failures should never break the user flow
            Log::error('MetaConversionsService: request failed.', [
                'event' => $eventName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    // ── Convenience Methods ─────────────────────────────────────────

    /**
     * Track a Lead event from an inscription.
     */
    public function trackLead(Inscription $inscription): bool
    {
        return $this->sendEvent(
            eventName: 'Lead',
            userData: [
                'em' => $inscription->email ?? '',
                'ph' => $inscription->whatsapp ?? '',
                'fn' => $inscription->nombre ?? '',
                'ln' => $inscription->apellido ?? '',
                'ct' => $inscription->ciudad ?? '',
                'country' => $inscription->pais ?? '',
            ],
            customData: [
                'content_name' => $inscription->plan?->value ?? 'unknown',
                'content_category' => 'inscription',
            ],
        );
    }

    /**
     * Track a Purchase event from a payment.
     */
    public function trackPurchase(Payment $payment): bool
    {
        return $this->sendEvent(
            eventName: 'Purchase',
            userData: [
                'em' => $payment->email ?? '',
                'ph' => $payment->buyer_phone ?? '',
                'fn' => $payment->buyer_name ?? '',
            ],
            customData: [
                'value' => (float) $payment->amount,
                'currency' => $payment->currency ?? 'COP',
                'content_name' => $payment->plan?->value ?? 'unknown',
                'content_category' => 'subscription',
            ],
        );
    }

    /**
     * Track a PageView event for the current request.
     */
    public function trackPageView(): bool
    {
        return $this->sendEvent(
            eventName: 'PageView',
        );
    }

    // ── Internals ───────────────────────────────────────────────────

    /**
     * Hash user data fields with SHA-256 per Meta requirements.
     *
     * Meta requires: em, ph, fn, ln, ct, country to be lowercase + SHA-256 hashed.
     *
     * @param  array<string, string>  $userData
     * @return array<string, string>
     */
    private function hashUserData(array $userData): array
    {
        $hashableFields = ['em', 'ph', 'fn', 'ln', 'ct', 'country'];
        $hashed = [];

        foreach ($userData as $key => $value) {
            if (in_array($key, $hashableFields, true) && $value !== '' && $value !== null) {
                // Normalize: trim, lowercase, then SHA-256
                $hashed[$key] = hash('sha256', strtolower(trim($value)));
            } elseif ($value !== '' && $value !== null) {
                // Non-hashable fields pass through as-is
                $hashed[$key] = $value;
            }
        }

        return $hashed;
    }
}
