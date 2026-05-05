<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ClientPulso;
use App\Services\CoachCommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

    public function announce(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $request->validate([
            'type' => 'required|in:post,push',
            'message' => 'required|string|max:1000',
            'pin_hours' => 'nullable|integer|min:1|max:168',
        ]);

        return response()->json(['todo' => 'announce-implementation-fase-b'], 501);
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
