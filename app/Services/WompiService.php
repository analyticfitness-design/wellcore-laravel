<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WompiService
{
    protected string $baseUrl;
    protected string $publicKey;
    protected string $privateKey;
    protected string $eventsSecret;

    public function __construct()
    {
        $this->baseUrl = config('wellcore.wompi.base_url', 'https://production.wompi.co/v1');
        $this->publicKey = config('wellcore.wompi.public_key', '');
        $this->privateKey = config('wellcore.wompi.private_key', '');
        $this->eventsSecret = config('wellcore.wompi.events_secret', '');
    }

    public function createPaymentLink(array $data): array
    {
        // TODO: Implement Wompi payment link creation
        return ['reference' => 'WC-' . uniqid(), 'url' => '#'];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $computed = hash('sha256', $payload . $this->eventsSecret);
        return hash_equals($computed, $signature);
    }

    public function getTransaction(string $transactionId): ?array
    {
        // TODO: Implement Wompi transaction lookup
        return null;
    }
}
