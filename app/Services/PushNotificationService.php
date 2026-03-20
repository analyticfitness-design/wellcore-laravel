<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected string $publicKey;
    protected string $privateKey;
    protected string $subject;

    public function __construct()
    {
        $this->publicKey = config('wellcore.vapid.public_key', '');
        $this->privateKey = config('wellcore.vapid.private_key', '');
        $this->subject = config('wellcore.vapid.subject', 'mailto:info@wellcorefitness.com');
    }

    public function sendToClient(int $clientId, string $title, string $body, ?string $url = null): int
    {
        $subscriptions = PushSubscription::where('client_id', $clientId)
            ->where('active', true)
            ->get();

        $sent = 0;
        foreach ($subscriptions as $sub) {
            try {
                // TODO: Implement actual Web Push sending with minishlink/web-push
                // For now, log the notification
                Log::info('Push notification', [
                    'client_id' => $clientId,
                    'title' => $title,
                    'endpoint' => substr($sub->endpoint, 0, 50) . '...',
                ]);
                $sent++;
            } catch (\Exception $e) {
                Log::error('Push failed', ['error' => $e->getMessage()]);
                $sub->update(['active' => false]);
            }
        }

        return $sent;
    }

    public function sendToAll(string $title, string $body, ?string $url = null): int
    {
        $total = 0;
        PushSubscription::where('active', true)
            ->select('client_id')
            ->distinct()
            ->chunk(100, function ($clients) use ($title, $body, $url, &$total) {
                foreach ($clients as $client) {
                    $total += $this->sendToClient($client->client_id, $title, $body, $url);
                }
            });
        return $total;
    }
}
