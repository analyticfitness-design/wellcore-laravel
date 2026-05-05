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
    /** Allowed query string values — guard against cache cardinality attacks */
    private const VALID_TIMES = ['today', 'week', 'all'];

    private const VALID_TYPES = ['all', 'pr', 'workout'];

    public function index(Request $request): JsonResponse|Response
    {
        $client = $this->resolveClientOrFail($request);

        $coachId = $this->resolveClientCoachId((int) $client->id);

        if ($coachId === null) {
            return response()->noContent();
        }

        $clientId = (int) $client->id;
        $scope = $request->query('scope', 'summary') === 'feed' ? 'feed' : 'summary';

        return $scope === 'feed'
            ? $this->feed($request, $coachId)
            : $this->summary($coachId, $clientId);
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

    /**
     * Summary card payload. Estrategia de cache de 2 niveles:
     *  - shared (per-coach, 30s) para stats + top_events + bpm + active_now
     *    [escrito por PrecomputeGroupPulse cron + lazy on miss]
     *  - per-client (30s) wraps el shared con user_vs_group del cliente.
     *
     * Antes (audit pre-fix) cada cliente tenía SU summary cacheado y el
     * precompute escribía a una key huérfana — Redis bloat sin beneficio.
     * Audit fix 2026-05-05.
     */
    private function summary(int $coachId, int $clientId): JsonResponse
    {
        $clientIds = $this->aggregator->resolveCoachClientIds($coachId);

        $sharedKey = "wc:group-pulse:v1:{$coachId}:summary:shared";
        $shared = Cache::remember($sharedKey, 30, function () use ($coachId, $clientIds) {
            $stats = $this->aggregator->computeStats($coachId, $clientIds);
            $events = $this->aggregator->buildFeed($coachId, 'today', 'all', $clientIds);
            // active_now: idealmente medida real-time (presence channel); fallback 0.
            $activeNow = (int) (Cache::get('community:active-now-count') ?? 0);
            $isQuiet = $stats['workouts_today'] === 0
                && $stats['prs_week'] === 0
                && $stats['achievements_today'] === 0;

            return [
                'active_now' => $activeNow,
                'bpm' => $isQuiet ? 50 : max(60, min(180, $stats['workouts_today'] * 4 + 60)),
                'is_quiet' => $isQuiet,
                'group_size' => $clientIds->count(),
                'stats' => $stats,
                'top_events' => array_slice($events, 0, 3),
            ];
        });

        // Wrap shared con la pieza per-client.
        $userVsGroup = $this->aggregator->userVsGroup($coachId, $clientId, $clientIds);

        return response()->json($shared + ['user_vs_group' => $userVsGroup]);
    }

    private function feed(Request $request, int $coachId): JsonResponse
    {
        $rawTime = (string) $request->query('time', 'today');
        $rawType = (string) $request->query('type', 'all');

        // Whitelist guards contra cache cardinality attack:
        // sin ellos, ?time=foo&type=bar genera N keys distintas con el mismo
        // payload (default = today/all en el match), inflando Redis.
        $time = in_array($rawTime, self::VALID_TIMES, true) ? $rawTime : 'today';
        $type = in_array($rawType, self::VALID_TYPES, true) ? $rawType : 'all';
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
