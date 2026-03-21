<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\WellcoreNotification;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    protected string $publicKey;
    protected string $privateKey;
    protected string $subject;

    /** Maximum retries per subscription endpoint */
    protected const MAX_RETRIES = 2;

    /** Base delay between retries in milliseconds */
    protected const RETRY_BASE_DELAY_MS = 500;

    public function __construct()
    {
        $this->publicKey = config('wellcore.vapid.public_key', '');
        $this->privateKey = config('wellcore.vapid.private_key', '');
        $this->subject = config('wellcore.vapid.subject', 'mailto:info@wellcorefitness.com');
    }

    // ─── STRUCTURED NOTIFICATION TYPES ─────────────────────────────────

    /**
     * Notify client about their weekly check-in reminder.
     */
    public static function notifyCheckinReminder(int $clientId): bool
    {
        return (new static)->send($clientId, [
            'title' => 'Check-in Semanal',
            'body' => 'Es hora de tu check-in semanal. Tu coach esta esperando tu reporte.',
            'icon' => '/images/logo-dark.png',
            'badge' => '/icons/icon-192x192.png',
            'tag' => 'checkin-reminder',
            'data' => ['url' => '/client/checkin', 'type' => 'checkin_reminder'],
            'actions' => [
                ['action' => 'open', 'title' => 'Ir al Check-in'],
                ['action' => 'dismiss', 'title' => 'Mas tarde'],
            ],
        ]);
    }

    /**
     * Notify client that their coach has responded to their check-in.
     */
    public static function notifyCoachResponse(int $clientId, string $coachName): bool
    {
        return (new static)->send($clientId, [
            'title' => 'Respuesta de tu Coach',
            'body' => "{$coachName} ha respondido a tu check-in. Revisa los comentarios.",
            'icon' => '/images/logo-dark.png',
            'badge' => '/icons/icon-192x192.png',
            'tag' => 'coach-response',
            'data' => ['url' => '/client/checkin', 'type' => 'coach_response'],
            'actions' => [
                ['action' => 'open', 'title' => 'Ver respuesta'],
                ['action' => 'dismiss', 'title' => 'Despues'],
            ],
        ]);
    }

    /**
     * Notify client that a new training/nutrition plan has been assigned.
     */
    public static function notifyNewPlan(int $clientId, string $planName): bool
    {
        return (new static)->send($clientId, [
            'title' => 'Nuevo Plan Asignado',
            'body' => "Tu coach te ha asignado un nuevo plan: {$planName}. Revisalo ahora!",
            'icon' => '/images/logo-dark.png',
            'badge' => '/icons/icon-192x192.png',
            'tag' => 'new-plan',
            'data' => ['url' => '/client/plan', 'type' => 'new_plan'],
            'actions' => [
                ['action' => 'open', 'title' => 'Ver plan'],
                ['action' => 'dismiss', 'title' => 'Despues'],
            ],
        ]);
    }

    /**
     * Notify client that their payment was confirmed.
     */
    public static function notifyPaymentConfirmed(int $clientId, string $planName, string $amount): bool
    {
        return (new static)->send($clientId, [
            'title' => 'Pago Confirmado',
            'body' => "Tu pago de {$amount} por {$planName} ha sido procesado exitosamente.",
            'icon' => '/images/logo-dark.png',
            'badge' => '/icons/icon-192x192.png',
            'tag' => 'payment-confirmed',
            'data' => ['url' => '/client/dashboard', 'type' => 'payment'],
        ]);
    }

    /**
     * Notify client about a training streak milestone.
     */
    public static function notifyStreakMilestone(int $clientId, int $days): bool
    {
        return (new static)->send($clientId, [
            'title' => "Racha de {$days} dias!",
            'body' => "Increible! Llevas {$days} dias consecutivos entrenando. Sigue asi!",
            'icon' => '/images/logo-dark.png',
            'badge' => '/icons/icon-192x192.png',
            'tag' => 'streak-milestone',
            'data' => ['url' => '/client/dashboard', 'type' => 'streak'],
        ]);
    }

    /**
     * Notify client about a new message in the community feed.
     */
    public static function notifyCommunityMention(int $clientId, string $mentionedBy): bool
    {
        return (new static)->send($clientId, [
            'title' => 'Te mencionaron en la comunidad',
            'body' => "{$mentionedBy} te ha mencionado en una publicacion. Mira lo que dijo!",
            'icon' => '/images/logo-dark.png',
            'badge' => '/icons/icon-192x192.png',
            'tag' => 'community-mention',
            'data' => ['url' => '/client/community', 'type' => 'community'],
        ]);
    }

    /**
     * Notify client about weekly summary availability.
     */
    public static function notifyWeeklySummary(int $clientId): bool
    {
        return (new static)->send($clientId, [
            'title' => 'Resumen Semanal Listo',
            'body' => 'Tu resumen de progreso semanal esta listo. Revisa tus metricas y logros!',
            'icon' => '/images/logo-dark.png',
            'badge' => '/icons/icon-192x192.png',
            'tag' => 'weekly-summary',
            'data' => ['url' => '/client/dashboard', 'type' => 'weekly_summary'],
        ]);
    }

    // ─── CORE SEND METHOD ──────────────────────────────────────────────

    /**
     * Send a push notification to a specific client with retry logic.
     *
     * @param int   $clientId  The client to notify
     * @param array $payload   Notification payload (title, body, icon, badge, tag, data, actions)
     * @return bool True if at least one notification was delivered
     */
    public function send(int $clientId, array $payload): bool
    {
        $subscriptions = PushSubscription::where('client_id', $clientId)
            ->where('active', true)
            ->get();

        if ($subscriptions->isEmpty()) {
            Log::debug('[PushNotification] No active subscriptions', ['client_id' => $clientId]);
            return false;
        }

        $delivered = false;

        foreach ($subscriptions as $sub) {
            $success = $this->sendToSubscription($sub, $payload);
            if ($success) {
                $delivered = true;
            }
        }

        // Log the notification for auditing
        $this->logNotification($clientId, $payload, $delivered);

        // Also create an in-app notification record
        $this->createInAppNotification($clientId, $payload);

        return $delivered;
    }

    /**
     * Send a push notification to all active subscribers (broadcast).
     *
     * @param array $payload Notification payload
     * @return int Number of successful deliveries
     */
    public function sendToAll(array $payload): int
    {
        $total = 0;

        PushSubscription::where('active', true)
            ->select('client_id')
            ->distinct()
            ->chunk(100, function ($clients) use ($payload, &$total) {
                foreach ($clients as $client) {
                    if ($this->send($client->client_id, $payload)) {
                        $total++;
                    }
                }
            });

        Log::info('[PushNotification] Broadcast sent', [
            'title' => $payload['title'] ?? 'N/A',
            'recipients' => $total,
        ]);

        return $total;
    }

    /**
     * Legacy-compatible method: send with simple title/body/url.
     * Kept for backward compatibility with existing callers.
     */
    public function sendToClient(int $clientId, string $title, string $body, ?string $url = null): int
    {
        $payload = [
            'title' => $title,
            'body' => $body,
            'icon' => '/images/logo-dark.png',
            'badge' => '/icons/icon-192x192.png',
            'tag' => 'general',
            'data' => ['url' => $url ?? '/client/dashboard', 'type' => 'general'],
        ];

        return $this->send($clientId, $payload) ? 1 : 0;
    }

    /**
     * Send a batch of notifications to multiple clients with the same payload.
     *
     * @param array $clientIds Array of client IDs
     * @param array $payload   Notification payload
     * @return array ['sent' => int, 'failed' => int]
     */
    public function sendBatch(array $clientIds, array $payload): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($clientIds as $clientId) {
            if ($this->send($clientId, $payload)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        Log::info('[PushNotification] Batch sent', [
            'title' => $payload['title'] ?? 'N/A',
            'sent' => $sent,
            'failed' => $failed,
        ]);

        return compact('sent', 'failed');
    }

    // ─── INTERNAL METHODS ──────────────────────────────────────────────

    /**
     * Send push notification to a single subscription endpoint with retry logic.
     *
     * Returns true if delivery was successful, false otherwise.
     * Handles expired/invalid subscriptions by deactivating them.
     */
    protected function sendToSubscription(PushSubscription $sub, array $payload): bool
    {
        // If VAPID keys are not configured, fall back to logging
        if (empty($this->publicKey) || empty($this->privateKey)) {
            Log::info('[PushNotification] VAPID keys not configured — logging only', [
                'client_id' => $sub->client_id,
                'title' => $payload['title'] ?? 'N/A',
                'endpoint' => substr($sub->endpoint, 0, 60) . '...',
            ]);
            return true; // Return true so in-app notification is still created
        }

        $attempt = 0;

        while ($attempt <= self::MAX_RETRIES) {
            try {
                $webPush = new WebPush([
                    'VAPID' => [
                        'subject' => $this->subject,
                        'publicKey' => $this->publicKey,
                        'privateKey' => $this->privateKey,
                    ],
                ]);

                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->p256dh,
                    'authToken' => $sub->auth_key,
                ]);

                $webPush->queueNotification($subscription, json_encode($payload));

                /** @var MessageSentReport $report */
                foreach ($webPush->flush() as $report) {
                    if ($report->isSuccess()) {
                        Log::debug('[PushNotification] Delivered', [
                            'client_id' => $sub->client_id,
                            'endpoint' => substr($sub->endpoint, 0, 60),
                        ]);
                        return true;
                    }

                    $statusCode = $report->getResponse()?->getStatusCode() ?? 0;

                    // 404 or 410 = subscription expired/invalid — deactivate it
                    if (in_array($statusCode, [404, 410], true)) {
                        Log::warning('[PushNotification] Subscription expired — deactivating', [
                            'client_id' => $sub->client_id,
                            'status' => $statusCode,
                            'endpoint' => substr($sub->endpoint, 0, 60),
                        ]);
                        $sub->update(['active' => false]);
                        return false;
                    }

                    // 429 (rate limit) or 5xx = retryable
                    if ($statusCode === 429 || $statusCode >= 500) {
                        $attempt++;
                        if ($attempt <= self::MAX_RETRIES) {
                            $delay = self::RETRY_BASE_DELAY_MS * pow(2, $attempt - 1);
                            usleep($delay * 1000);
                            Log::debug('[PushNotification] Retrying', [
                                'attempt' => $attempt,
                                'status' => $statusCode,
                                'client_id' => $sub->client_id,
                            ]);
                            continue;
                        }
                    }

                    // Non-retryable error
                    Log::error('[PushNotification] Delivery failed', [
                        'client_id' => $sub->client_id,
                        'status' => $statusCode,
                        'reason' => $report->getReason(),
                        'endpoint' => substr($sub->endpoint, 0, 60),
                    ]);
                    return false;
                }
            } catch (\Exception $e) {
                $attempt++;
                Log::error('[PushNotification] Exception on attempt ' . $attempt, [
                    'client_id' => $sub->client_id,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt > self::MAX_RETRIES) {
                    // After all retries exhausted, deactivate if it looks permanent
                    if (str_contains($e->getMessage(), 'expired') || str_contains($e->getMessage(), 'unsubscribed')) {
                        $sub->update(['active' => false]);
                    }
                    return false;
                }

                $delay = self::RETRY_BASE_DELAY_MS * pow(2, $attempt - 1);
                usleep($delay * 1000);
            }
        }

        return false;
    }

    /**
     * Log the notification for auditing purposes.
     */
    protected function logNotification(int $clientId, array $payload, bool $delivered): void
    {
        Log::channel('daily')->info('[PushNotification] Audit log', [
            'client_id' => $clientId,
            'title' => $payload['title'] ?? 'N/A',
            'tag' => $payload['tag'] ?? 'general',
            'type' => $payload['data']['type'] ?? 'unknown',
            'delivered' => $delivered,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Create an in-app notification record in the notifications table
     * so the NotificationBell component can display it.
     */
    protected function createInAppNotification(int $clientId, array $payload): void
    {
        try {
            WellcoreNotification::create([
                'user_type' => 'client',
                'user_id' => $clientId,
                'type' => $payload['data']['type'] ?? 'general',
                'title' => $payload['title'] ?? 'WellCore Fitness',
                'body' => $payload['body'] ?? null,
                'link' => $payload['data']['url'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::warning('[PushNotification] Could not create in-app notification', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove all expired/inactive subscriptions for cleanup.
     * Can be called from a scheduled command.
     */
    public static function cleanupExpiredSubscriptions(): int
    {
        $deleted = PushSubscription::where('active', false)->delete();

        Log::info('[PushNotification] Cleanup: removed expired subscriptions', [
            'count' => $deleted,
        ]);

        return $deleted;
    }

    /**
     * Get the count of active push subscriptions for a client.
     */
    public static function getSubscriptionCount(int $clientId): int
    {
        return PushSubscription::where('client_id', $clientId)
            ->where('active', true)
            ->count();
    }
}
