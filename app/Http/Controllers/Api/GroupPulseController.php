<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\AssignedPlan;
use App\Models\CoachMessage;
use App\Services\GroupPulseAggregator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $coachId = $this->resolveClientCoachId((int) $client->id);

        if ($coachId === null) {
            return response()->noContent();
        }

        $clientId = (int) $client->id;
        $scope = $request->query('scope', 'summary');

        return match ($scope) {
            'feed' => $this->feed($request, $coachId),
            default => $this->summary($coachId, $clientId),
        };
    }

    /**
     * Resolve coach_id for a client using the 3-fallback rule (matches
     * ClientController::myCoach). clients.coach_id is sparsely populated
     * in production — most assignments live in assigned_plans.assigned_by
     * or coach_messages.coach_id.
     */
    private function resolveClientCoachId(int $clientId): ?int
    {
        if (Schema::hasColumn('clients', 'coach_id')) {
            $direct = DB::table('clients')->where('id', $clientId)->value('coach_id');
            if ($direct) {
                return (int) $direct;
            }
        }

        if (Schema::hasTable('assigned_plans')) {
            $fromPlan = AssignedPlan::where('client_id', $clientId)
                ->whereNotNull('assigned_by')
                ->orderByDesc('valid_from')
                ->value('assigned_by');
            if ($fromPlan) {
                return (int) $fromPlan;
            }
        }

        if (Schema::hasTable('coach_messages')) {
            $fromMsg = CoachMessage::where('client_id', $clientId)
                ->whereNotNull('coach_id')
                ->orderByDesc('created_at')
                ->value('coach_id');
            if ($fromMsg) {
                return (int) $fromMsg;
            }
        }

        return null;
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
