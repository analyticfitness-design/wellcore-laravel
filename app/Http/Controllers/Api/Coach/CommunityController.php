<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Coach;

use App\Events\BroadcastSent;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BroadcastMessage;
use App\Models\Client;
use App\Models\ClientPulso;
use App\Models\CommunityPost;
use App\Models\ModerationAction;
use App\Services\CoachCommunityService;
use App\Services\ModerationService;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommunityController extends Controller
{
    public function __construct(private CoachCommunityService $service) {}

    public function pulse(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $payload = Cache::remember(
            key: "wc:coach-pulse:v1:{$coach->id}",
            ttl: 60,
            callback: fn () => [
                'team_health_score' => $this->service->teamHealthScore($coach->id),
                'top_performers' => $this->service->topPerformers($coach->id, days: 7, limit: 3),
                'at_risk_clients' => $this->service->atRiskClients($coach->id, days: 5),
                'computed_at' => now()->toIso8601String(),
            ],
        );

        return response()->json($payload);
    }

    public function posts(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $filter = (string) $request->query('filter', 'all');
        $perPage = min(50, max(5, (int) $request->query('per_page', 20)));

        $page = $this->service->getFeed($coach->id, $filter, $perPage);

        return response()->json($page);
    }

    public function pulsos(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $clientIds = $this->service->resolveClientIds($coach->id);

        $pulsos = ClientPulso::query()
            ->whereIn('client_id', $clientIds)
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json(['data' => $pulsos]);
    }

    public function threads(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $sinceDays = (int) $request->query('since_days', 7);
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(5, (int) $request->query('per_page', 20)));

        $payload = $this->service->threads($coach->id, $sinceDays, $page, $perPage);

        return response()->json($payload);
    }

    public function achievements(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $period = (string) $request->query('period', 'week');
        if (! in_array($period, ['week', 'month', 'all'], true)) {
            $period = 'week';
        }
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(5, (int) $request->query('per_page', 20)));

        $payload = $this->service->achievements($coach->id, $period, $page, $perPage);

        return response()->json($payload);
    }

    public function announce(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $validated = $request->validate([
            'type' => 'required|in:post,push',
            'message' => 'required|string|max:1000',
            'pin_hours' => 'nullable|integer|min:1|max:168',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'plan_filter' => 'nullable|json',
        ]);

        if ($validated['type'] === 'post') {
            return $this->announceAsPost($coach, $validated, $request);
        }

        return $this->announceAsPush($coach, $validated);
    }

    private function announceAsPost(Admin $coach, array $data, Request $request): JsonResponse
    {
        return DB::transaction(function () use ($coach, $data, $request) {
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('community/announcements', 'public');
                $imageUrl = Storage::url($path);
            }

            $post = CommunityPost::create([
                'client_id' => null,
                'coach_admin_id' => $coach->id,
                'author_type' => 'coach',
                'author_admin_id' => $coach->id,
                'is_official' => true,
                'is_global' => false,
                'content' => $data['message'],
                'image_url' => $imageUrl,
                'visible' => true,
            ]);

            if (! empty($data['pin_hours'])) {
                app(ModerationService::class)->pinPost(
                    $post, $coach, 'coach', (int) $data['pin_hours'], 'Anuncio al equipo'
                );
            }

            ModerationAction::create([
                'actor_type' => 'coach',
                'actor_id' => $coach->id,
                'action_type' => 'announce',
                'target_type' => 'post',
                'target_id' => $post->id,
                'metadata' => ['mode' => 'post', 'pin_hours' => $data['pin_hours'] ?? null],
                'created_at' => now(),
            ]);

            $clientIds = $this->service->resolveClientIds($coach->id);
            $count = count($clientIds);

            BroadcastMessage::create([
                'sender_type' => 'coach',
                'sender_id' => $coach->id,
                'audience_type' => 'clients',
                'segment_filter' => null,
                'subject' => null,
                'body' => $data['message'],
                'push_enabled' => false,
                'recipients_count' => $count,
                'delivered_count' => $count,
                'sent_at' => now(),
            ]);

            event(new BroadcastSent($post->id, 'announcement_post', $count));

            return response()->json([
                'post_id' => $post->id,
                'recipients_count' => $count,
                'pinned_until' => $post->pinned?->pinned_until,
            ], 201);
        });
    }

    private function announceAsPush(Admin $coach, array $data): JsonResponse
    {
        $segmentFilter = isset($data['plan_filter']) ? json_decode($data['plan_filter'], true) : null;
        $clientIds = $this->service->resolveClientIds($coach->id);

        if (is_array($segmentFilter) && ! empty($segmentFilter['plan'])) {
            $clientIds = Client::query()
                ->whereIn('id', $clientIds)
                ->whereIn('plan', $segmentFilter['plan'])
                ->pluck('id')
                ->all();
        }

        $delivered = app(PushNotificationService::class)
            ->notifyCoachAnnounceToClients(
                coachId: $coach->id,
                clientIds: $clientIds,
                message: $data['message']
            );

        BroadcastMessage::create([
            'sender_type' => 'coach',
            'sender_id' => $coach->id,
            'audience_type' => 'clients',
            'segment_filter' => $segmentFilter,
            'subject' => 'Anuncio del coach',
            'body' => $data['message'],
            'push_enabled' => true,
            'recipients_count' => count($clientIds),
            'delivered_count' => $delivered,
            'sent_at' => now(),
        ]);

        ModerationAction::create([
            'actor_type' => 'coach',
            'actor_id' => $coach->id,
            'action_type' => 'announce',
            'target_type' => 'post',
            'target_id' => 0,
            'metadata' => ['mode' => 'push', 'segment' => $segmentFilter, 'count' => $delivered],
            'created_at' => now(),
        ]);

        event(new BroadcastSent(0, 'announcement_push', $delivered));

        return response()->json([
            'recipients_count' => count($clientIds),
            'delivered_count' => $delivered,
        ], 201);
    }

    private function isCoach(mixed $user): bool
    {
        if (! $user instanceof Admin) {
            return false;
        }

        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        return $role === 'coach';
    }
}
