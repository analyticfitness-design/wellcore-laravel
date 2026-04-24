<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Http\Resources\MedalResource;
use App\Models\Client;
use App\Models\ClientXp;
use App\Models\Medal;
use App\Services\MedalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Client-facing medal endpoints.
 *
 * Auth is enforced via resolveClientOrFail(): the resulting Client is the
 * authenticated user. All queries scope by $client->id — no external IDs
 * are ever taken from the request, so IDOR is structurally impossible here.
 */
class MedalController extends Controller
{
    use AuthenticatesVueRequests;

    public function __construct(private readonly MedalService $medals) {}

    /**
     * GET /api/v/client/medals
     * Returns all active medals + the client's state (achieved / progress) for each.
     */
    public function index(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        // Re-evaluate on read so the UI is always correct even if an event was missed.
        try {
            $this->medals->checkAll($client);
        } catch (\Throwable $e) {
            // Medals are a read-side enhancement — never block the response if a resolver fails.
            \Log::warning('medal.checkAll.failed', ['client_id' => $client->id, 'err' => $e->getMessage()]);
        }

        $medals = $this->hydrateMedalsWithPivot($client);

        $achievedCount = $medals->filter(
            static fn (Medal $m) => $m->pivot && $m->pivot->achieved_at !== null,
        )->count();

        return response()->json([
            'stats' => $this->buildStats($client, $achievedCount, $medals->count()),
            'medals' => MedalResource::collection($medals),
        ]);
    }

    /**
     * GET /api/v/client/medals/unlocked
     * Only the medals the client has already achieved.
     */
    public function unlocked(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $medals = $this->hydrateMedalsWithPivot($client)
            ->filter(static fn (Medal $m) => $m->pivot && $m->pivot->achieved_at !== null)
            ->values();

        return response()->json([
            'stats' => $this->buildStats($client, $medals->count(), Medal::active()->count()),
            'medals' => MedalResource::collection($medals),
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Loads all active medals, then hydrates $medal->pivot from client_medals
     * in a single extra query (no N+1).
     */
    private function hydrateMedalsWithPivot(Client $client)
    {
        $medals = Medal::active()->ordered()->get();

        $pivots = DB::table('client_medals')
            ->where('client_id', $client->id)
            ->get()
            ->keyBy('medal_id');

        return $medals->each(function (Medal $medal) use ($pivots) {
            $medal->pivot = $pivots->get($medal->id);
        });
    }

    /**
     * Computes display stats: level/XP curve, streak, totals.
     *
     * Reads XP from client_xp.xp_total (same source as Dashboard) so both
     * views show the same number. Level formula: floor(xp / 200) + 1, with
     * each level requiring 200 XP — matches TrainingController/ClientController.
     */
    private function buildStats(Client $client, int $achievedMedals, int $totalMedals): array
    {
        $clientXpRow = ClientXp::where('client_id', $client->id)->first();
        $totalXp = (int) ($clientXpRow?->xp_total ?? 0);
        $level = max(1, (int) floor($totalXp / 200) + 1);
        $xpForCurrentLevel = ($level - 1) * 200;

        $displayName = $this->displayName($client);
        $streak = (int) ($clientXpRow?->streak_days ?? $client->current_streak ?? 0);
        $totalWorkouts = (int) ($client->total_workouts ?? 0);

        return [
            'displayName' => $displayName,
            'avatarInitial' => mb_strtoupper(mb_substr($displayName, 0, 1)),
            'streak' => $streak,
            'totalWorkouts' => $totalWorkouts,
            'totalXP' => $totalXp,
            'level' => $level,
            'xpCurrentLevel' => max(0, $totalXp - $xpForCurrentLevel),
            'xpNextLevel' => 200,
            'achievedMedals' => $achievedMedals,
            'totalMedals' => $totalMedals,
        ];
    }

    private function displayName(Client $client): string
    {
        $name = trim((string) $client->name);

        if ($name === '') {
            return 'Cliente';
        }

        $first = explode(' ', $name)[0] ?? $name;

        return $first !== '' ? $first : 'Cliente';
    }
}
