<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Services\GroupPulseAggregator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class GroupPulseController extends Controller
{
    use AuthenticatesVueRequests;

    public function __construct(private readonly GroupPulseAggregator $aggregator) {}

    /**
     * GET /api/v/client/group-pulse?scope=summary|feed
     *
     * Returns either a summary card payload (default) or a paginated event
     * feed. Both are scoped to the authenticated client's coach — no clients
     * outside that coach's group are ever exposed.
     *
     * 204 when the client has no coach assigned (orphan, e.g. fresh signup
     * without coach yet) so the frontend can hide the section cleanly.
     */
    public function index(Request $request): JsonResponse|Response
    {
        $client = $this->resolveClientOrFail($request);

        if (! $client->coach_id) {
            return response()->noContent();
        }

        $coachId = (int) $client->coach_id;
        $clientId = (int) $client->id;
        $scope = $request->query('scope', 'summary');

        return match ($scope) {
            'feed' => $this->feed($request, $coachId),
            default => $this->summary($coachId, $clientId),
        };
    }

    private function summary(int $coachId, int $clientId): JsonResponse
    {
        $key = "wc:group-pulse:v1:{$coachId}:summary:{$clientId}";

        $payload = Cache::remember($key, 30, function () use ($coachId, $clientId) {
            $stats = $this->aggregator->computeStats($coachId);
            $events = $this->aggregator->buildFeed($coachId, 'today', 'all');
            $activeNow = (int) (Cache::get('community:active-list-count') ?? 0);
            $bpm = max(40, min(180, $stats['workouts_today'] * 4 + 40));

            return [
                'active_now' => $activeNow,
                'bpm' => $bpm,
                'stats' => $stats,
                'top_events' => array_slice($events, 0, 3),
                'user_vs_group' => $this->aggregator->userVsGroup($coachId, $clientId),
            ];
        });

        return response()->json($payload);
    }

    private function feed(Request $request, int $coachId): JsonResponse
    {
        $time = $request->query('time', 'today');
        $type = $request->query('type', 'all');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(20, max(5, (int) $request->query('per_page', 10)));

        $key = "wc:group-pulse:v1:{$coachId}:feed:{$time}:{$type}:{$page}:{$perPage}";

        $payload = Cache::remember($key, 60, function () use ($coachId, $time, $type, $page, $perPage) {
            $all = $this->aggregator->buildFeed($coachId, $time, $type);
            $total = count($all);
            $offset = ($page - 1) * $perPage;

            return [
                'events' => array_slice($all, $offset, $perPage),
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => max(1, (int) ceil($total / $perPage)),
                    'total' => $total,
                ],
            ];
        });

        return response()->json($payload);
    }
}
