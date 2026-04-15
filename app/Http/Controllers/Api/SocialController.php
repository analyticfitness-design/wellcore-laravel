<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Mail\ReferralInvitation;
use App\Models\AssignedPlan;
use App\Models\VideoCheckin;
use App\Models\BiometricLog;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\CoachMessage;
use App\Models\CommunityPost;
use App\Models\AcademyContent;
use App\Models\CoachVideoTip;
use App\Models\FoodAnalysis;
use App\Models\HabitLog;
use App\Models\PersonalRecord;
use App\Models\PostComment;
use App\Models\PostReaction;
use App\Models\ProgressPhoto;
use App\Models\Referral;
use App\Models\SupplementLog;
use App\Services\AIService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SocialController extends Controller
{
    use AuthenticatesVueRequests;

    // ─── Community Feed ────────────────────────────────────────────────

    /**
     * GET /api/v/client/community
     *
     * Community feed posts with reactions/comments.
     * Ports CommunityFeed.php render() logic.
     */
    public function communityIndex(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(5, (int) $request->query('per_page', 10)));

        $communityStats = Cache::remember('community:stats', 300, function () {
            return [
                'total_posts'    => CommunityPost::where('visible', true)->count(),
                'active_members' => Client::where('status', 'activo')->count(),
            ];
        });

        $posts = CommunityPost::where('visible', true)
            ->withCount(['reactions', 'comments'])
            ->with([
                'client:id,name',
                'comments.client:id,name',
                'reactions' => fn ($q) => $q->where('client_id', $clientId),
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $postIds = $posts->pluck('id');

        // Per-type reaction counts
        $reactionCountsAll = PostReaction::whereIn('post_id', $postIds)
            ->selectRaw('post_id, reaction_type, COUNT(*) as total')
            ->groupBy('post_id', 'reaction_type')
            ->get()
            ->groupBy('post_id')
            ->map(fn ($rows) => $rows->pluck('total', 'reaction_type'));

        // My reactions per post
        $myReactions = $posts->getCollection()
            ->mapWithKeys(fn ($post) => [
                $post->id => $post->reactions->pluck('reaction_type')->toArray(),
            ]);

        $postsData = $posts->getCollection()->map(fn ($post) => [
            'id'              => $post->id,
            'client_id'       => $post->client_id,
            'client_name'     => $post->client?->name ?? 'Anonimo',
            'content'         => $post->content,
            'post_type'       => $post->post_type,
            'created_at'      => $post->created_at?->toIso8601String(),
            'reactions_count'  => $post->reactions_count,
            'comments_count'   => $post->comments_count,
            'my_reactions'     => $myReactions[$post->id] ?? [],
            'reaction_counts'  => $reactionCountsAll[$post->id] ?? [],
            'comments'         => $post->comments->map(fn ($c) => [
                'id'          => $c->id,
                'client_name' => $c->client?->name ?? 'Anonimo',
                'client_id'   => $c->client_id,
                'content'     => $c->content,
                'created_at'  => $c->created_at?->toIso8601String(),
            ])->toArray(),
        ])->toArray();

        return response()->json([
            'posts'           => $postsData,
            'community_stats' => $communityStats,
            'pagination'      => [
                'current_page' => $posts->currentPage(),
                'last_page'    => $posts->lastPage(),
                'per_page'     => $posts->perPage(),
                'total'        => $posts->total(),
            ],
        ]);
    }

    /**
     * POST /api/v/client/community
     *
     * Create a new community post. Ports CommunityFeed.php createPost().
     */
    public function communityCreate(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'content'   => 'required|string|max:1000',
            'post_type' => 'required|in:text,achievement,pr,photo',
        ]);

        $content  = $request->input('content');
        $postType = $request->input('post_type');

        if ($postType === 'achievement' && ! str_starts_with($content, 'Logro: ')) {
            $content = 'Logro: ' . $content;
        } elseif ($postType === 'pr' && ! str_starts_with($content, 'Nuevo PR: ')) {
            $content = 'Nuevo PR: ' . $content;
        }

        $post = CommunityPost::create([
            'client_id' => $clientId,
            'content'   => $content,
            'post_type' => $postType,
        ]);

        return response()->json([
            'id'         => $post->id,
            'created_at' => $post->created_at?->toIso8601String(),
        ], 201);
    }

    /**
     * POST /api/v/client/community/{id}/react
     *
     * Toggle reaction on a post. Ports CommunityFeed.php toggleReaction().
     */
    public function communityReact(Request $request, int $id): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'reaction_type' => 'required|string|max:20',
        ]);

        $reactionType = $request->input('reaction_type');

        $existing = PostReaction::where('post_id', $id)
            ->where('client_id', $clientId)
            ->where('reaction_type', $reactionType)
            ->first();

        if ($existing) {
            $existing->delete();
            $toggled = false;
        } else {
            PostReaction::create([
                'post_id'       => $id,
                'client_id'     => $clientId,
                'reaction_type' => $reactionType,
            ]);
            $toggled = true;
        }

        return response()->json([
            'toggled'       => $toggled,
            'reaction_type' => $reactionType,
        ]);
    }

    /**
     * POST /api/v/client/community/{id}/comment
     *
     * Add comment to a post. Ports CommunityFeed.php addComment().
     */
    public function communityComment(Request $request, int $id): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'content' => 'required|string|min:1|max:500',
        ]);

        $comment = PostComment::create([
            'post_id'   => $id,
            'client_id' => $clientId,
            'content'   => $request->input('content'),
        ]);

        return response()->json([
            'id'          => $comment->id,
            'client_name' => $client->name ?? 'Anonimo',
            'created_at'  => $comment->created_at?->toIso8601String(),
        ], 201);
    }

    /**
     * DELETE /api/v/client/community/{id}
     *
     * Soft-delete a post (set visible=false). Only the post owner may delete.
     * Ports CommunityFeed.php deletePost().
     */
    public function communityDelete(Request $request, int $id): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $deleted = CommunityPost::where('id', $id)
            ->where('client_id', $clientId)
            ->update(['visible' => false]);

        if (! $deleted) {
            return response()->json(['message' => 'Post no encontrado o no tienes permiso'], 404);
        }

        return response()->json(['deleted' => true]);
    }

    // ─── Challenges ────────────────────────────────────────────────────

    /**
     * GET /api/v/client/challenges
     *
     * Active challenges with participation status. Ports ChallengesView.php render().
     */
    public function challenges(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $challenges = Challenge::where('is_active', true)
            ->with([
                'participants' => fn ($q) => $q->where('client_id', $clientId)
                    ->select(['id', 'challenge_id', 'client_id', 'progress', 'completed', 'joined_at']),
            ])
            ->orderByDesc('start_date')
            ->limit(50)
            ->get();

        $data = $challenges->map(function (Challenge $challenge) {
            $participation = $challenge->participants->first();
            $progressPct   = ($participation && $challenge->goal_value)
                ? min(100, round(($participation->progress / $challenge->goal_value) * 100, 1))
                : 0;

            return [
                'id'             => $challenge->id,
                'title'          => $challenge->title,
                'description'    => $challenge->description,
                'start_date'     => $challenge->start_date?->format('Y-m-d'),
                'end_date'       => $challenge->end_date?->format('Y-m-d'),
                'goal_value'     => $challenge->goal_value,
                'goal_unit'      => $challenge->goal_unit,
                'reward'         => $challenge->reward,
                'joined'         => $participation !== null,
                'completed'      => $participation?->completed ?? false,
                'progress'       => $participation?->progress ?? 0,
                'progress_pct'   => $progressPct,
                'participation'  => $participation ? [
                    'id'        => $participation->id,
                    'joined_at' => $participation->joined_at,
                ] : null,
            ];
        })->toArray();

        return response()->json(['challenges' => $data]);
    }

    /**
     * POST /api/v/client/challenges/{id}/join
     *
     * Join a challenge. Ports ChallengesView.php join().
     */
    public function joinChallenge(Request $request, int $id): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $exists = ChallengeParticipant::where('challenge_id', $id)
            ->where('client_id', $clientId)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Ya estas participando en este reto.'], 422);
        }

        $challenge = Challenge::where('id', $id)
            ->where('is_active', true)
            ->first();

        if (! $challenge) {
            return response()->json(['error' => 'Reto no encontrado o inactivo.'], 404);
        }

        $participant = ChallengeParticipant::create([
            'challenge_id' => $id,
            'client_id'    => $clientId,
            'progress'     => 0,
            'completed'    => false,
            'joined_at'    => now(),
        ]);

        return response()->json([
            'joined'         => true,
            'participation_id' => $participant->id,
        ], 201);
    }

    // ─── Chat ──────────────────────────────────────────────────────────

    /**
     * GET /api/v/client/chat
     *
     * Chat messages with coach. Ports ChatWidget.php.
     */
    public function chatIndex(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        // Determine coach from most recent assigned plan
        $assignedPlan = AssignedPlan::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->first();

        $coach    = null;
        $hasCoach = false;
        if ($assignedPlan && $assignedPlan->assigned_by) {
            $coach = \App\Models\Admin::find($assignedPlan->assigned_by);
        }

        if (! $coach) {
            return response()->json([
                'has_coach'  => false,
                'coach_name' => 'Coach no asignado',
                'messages'   => [],
            ]);
        }

        $hasCoach  = true;
        $coachId   = $coach->id;
        $coachName = $coach->name ?? 'Coach';

        // Mark unread messages as read
        CoachMessage::where('client_id', $clientId)
            ->where('coach_id', $coachId)
            ->where('direction', 'coach_to_client')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = CoachMessage::where('client_id', $clientId)
            ->where('coach_id', $coachId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($m) => [
                'id'         => $m->id,
                'message'    => $m->message,
                'direction'  => $m->direction,
                'read_at'    => $m->read_at,
                'created_at' => $m->created_at?->toIso8601String(),
            ])
            ->toArray();

        return response()->json([
            'has_coach'  => $hasCoach,
            'coach_id'   => $coachId,
            'coach_name' => $coachName,
            'messages'   => $messages,
        ]);
    }

    /**
     * POST /api/v/client/chat
     *
     * Send message to coach. Ports ChatWidget.php sendMessage().
     */
    public function chatSend(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'message' => 'required|string|min:1|max:2000',
        ]);

        // Determine coach
        $assignedPlan = AssignedPlan::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->first();

        $coach = null;
        if ($assignedPlan && $assignedPlan->assigned_by) {
            $coach = \App\Models\Admin::find($assignedPlan->assigned_by);
        }

        if (! $coach) {
            return response()->json(['error' => 'No tienes un coach asignado.'], 422);
        }

        $msg = CoachMessage::create([
            'coach_id'  => $coach->id,
            'client_id' => $clientId,
            'message'   => $request->input('message'),
            'direction' => 'client_to_coach',
        ]);

        return response()->json([
            'id'         => $msg->id,
            'created_at' => $msg->created_at?->toIso8601String(),
        ], 201);
    }

    // ─── Nutrition ─────────────────────────────────────────────────────

    /**
     * GET /api/v/client/nutrition
     *
     * Nutrition plan with meals, macros, water tracking. Ports NutritionPlan.php.
     */
    public function nutrition(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $assignedPlan = AssignedPlan::where('client_id', $clientId)
            ->where('plan_type', 'nutricion')
            ->where('active', true)
            ->latest()
            ->first();

        $plan = null;
        if ($assignedPlan && $assignedPlan->content) {
            $plan = is_array($assignedPlan->content)
                ? $assignedPlan->content
                : json_decode($assignedPlan->content, true);
        }

        $macros    = $this->parseMacros($plan);
        $meals     = $this->parseMeals($plan);
        $extras    = $this->parseExtras($plan);
        $water     = $this->loadWaterData($clientId, $plan);
        $weight    = $this->loadWeightData($clientId, $plan);

        // Show onboarding tutorial if client has never logged a biometric weight
        $showTutorial = ! BiometricLog::where('client_id', $clientId)
            ->whereNotNull('weight_kg')
            ->where('weight_kg', '>', 0)
            ->exists();

        return response()->json([
            'has_plan'        => $plan !== null,
            'plan_raw'        => $plan,
            'macros'          => $macros,
            'meals'           => $meals,
            'extras'          => $extras,
            'water'           => $water,
            'weight'          => $weight,
            'show_tutorial'   => $showTutorial,
        ]);
    }

    /**
     * POST /api/v/client/nutrition/dismiss-tutorial
     *
     * Dismiss the nutrition onboarding tutorial (no-op server-side,
     * the tutorial auto-dismisses once client logs a weight).
     */
    public function dismissNutritionTutorial(Request $request): JsonResponse
    {
        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/v/client/nutrition/water
     *
     * Toggle water intake. Ports NutritionPlan.php toggleWater().
     */
    public function toggleWater(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'amount' => 'nullable|integer|min:1|max:5000',
        ]);

        $amount = (int) $request->input('amount', 250);
        $today  = now()->toDateString();

        $log = HabitLog::where('client_id', $clientId)
            ->where('habit_type', 'agua')
            ->where('log_date', $today)
            ->first();

        if ($log) {
            $log->value += $amount;
            $log->save();
            $newValue = (int) $log->value;
        } else {
            HabitLog::create([
                'client_id'  => $clientId,
                'log_date'   => $today,
                'habit_type' => 'agua',
                'value'      => $amount,
            ]);
            $newValue = $amount;
        }

        return response()->json([
            'water_consumed_ml' => $newValue,
        ]);
    }

    // ─── Habits ────────────────────────────────────────────────────────

    /**
     * GET /api/v/client/habits
     *
     * Habit tracker data. Ports HabitTracker.php render().
     */
    public function habits(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $today      = now()->toDateString();
        $todayCarbon = Carbon::today();

        $habitDefs = [
            'agua'          => ['label' => 'Agua', 'icon' => 'water', 'tip' => 'Tu cuerpo necesita al menos 2 litros diarios para un rendimiento optimo.'],
            'sueno'         => ['label' => 'Sueno', 'icon' => 'moon', 'tip' => 'Dormir 7-9 horas mejora tu recuperacion muscular y equilibrio hormonal.'],
            'entrenamiento' => ['label' => 'Entrenamiento', 'icon' => 'dumbbell', 'tip' => 'La consistencia supera la intensidad. Cada sesion cuenta.'],
            'nutricion'     => ['label' => 'Nutricion', 'icon' => 'apple', 'tip' => 'Cumplir tu plan de nutricion es lo que realmente transforma tu cuerpo.'],
            'suplementos'   => ['label' => 'Suplementos', 'icon' => 'pill', 'tip' => 'Toma tus suplementos a la misma hora cada dia para maxima absorcion.'],
        ];

        $totalHabits = count($habitDefs);

        // Today's logs
        $todayLogs = HabitLog::where('client_id', $clientId)
            ->where('log_date', $today)
            ->get()
            ->keyBy('habit_type');

        // Last 90 days for streaks
        $last90Logs = HabitLog::where('client_id', $clientId)
            ->where('log_date', '>=', now()->subDays(90)->toDateString())
            ->where('value', '>=', 1)
            ->get();

        $todayHabits = [];
        foreach ($habitDefs as $type => $meta) {
            $todayLogged = $last90Logs->contains(fn ($l) =>
                $l->habit_type === $type &&
                $l->log_date->format('Y-m-d') === $today
            );
            $streak    = 0;
            $checkDate = $todayLogged ? now()->copy() : now()->subDay();
            for ($i = 0; $i < 90; $i++) {
                $hasLog = $last90Logs->contains(fn ($l) =>
                    $l->habit_type === $type &&
                    $l->log_date->format('Y-m-d') === $checkDate->format('Y-m-d')
                );
                if ($hasLog) {
                    $streak++;
                    $checkDate->subDay();
                } else {
                    break;
                }
            }

            $thirtyDaysAgo = now()->subDays(30)->toDateString();
            $daysCompleted = $last90Logs
                ->where('habit_type', $type)
                ->filter(fn ($l) => $l->log_date->toDateString() >= $thirtyDaysAgo)
                ->count();
            $compliance = min(100, round(($daysCompleted / 30) * 100));

            $logEntry  = $todayLogs[$type] ?? null;
            $completed = $logEntry !== null && (int) $logEntry->value >= 1;

            $todayHabits[$type] = [
                'label'      => $meta['label'],
                'icon'       => $meta['icon'],
                'tip'        => $meta['tip'],
                'completed'  => $completed,
                'streak'     => $streak,
                'compliance' => $compliance,
            ];
        }

        $completedToday = collect($todayHabits)->where('completed', true)->count();

        // Weekly overview
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekLogs    = HabitLog::where('client_id', $clientId)
            ->whereBetween('log_date', [
                $startOfWeek->toDateString(),
                $startOfWeek->copy()->addDays(6)->toDateString(),
            ])
            ->get()
            ->groupBy(fn ($log) => $log->log_date->format('Y-m-d'));

        $weeklyData             = [];
        $weeklyPossibleDays     = 0;
        $weeklyCompletedHabits  = 0;

        for ($i = 0; $i < 7; $i++) {
            $day         = $startOfWeek->copy()->addDays($i);
            $dateKey     = $day->format('Y-m-d');
            $isFutureDay = $day->gt($todayCarbon);
            $dayLogs     = $weekLogs->get($dateKey, collect());
            $dayCompleted = $dayLogs->filter(fn ($l) => (int) $l->value >= 1)->count();

            if (! $isFutureDay) {
                $weeklyPossibleDays++;
                $weeklyCompletedHabits += $dayCompleted;
            }

            $weeklyData[] = [
                'date'      => $dateKey,
                'dayName'   => $day->locale('es')->isoFormat('dd'),
                'dayNumber' => $day->format('d'),
                'isToday'   => $day->isToday(),
                'isFuture'  => $isFutureDay,
                'completed' => $dayCompleted,
                'total'     => $totalHabits,
            ];
        }

        $weeklyComplianceMax = $weeklyPossibleDays * $totalHabits;
        $weeklyCompliance    = $weeklyComplianceMax > 0
            ? min(100, (int) round(($weeklyCompletedHabits / $weeklyComplianceMax) * 100))
            : 0;

        // Monthly heatmap (last 30 days)
        $heatmapCounts = HabitLog::where('client_id', $clientId)
            ->where('log_date', '>=', now()->subDays(29)->toDateString())
            ->where('value', '>=', 1)
            ->selectRaw('DATE(log_date) as date_key, COUNT(*) as cnt')
            ->groupBy('date_key')
            ->pluck('cnt', 'date_key');

        $heatmapData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date    = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $dayLogs = $heatmapCounts->get($dateKey, 0);

            $heatmapData[] = [
                'date'  => $dateKey,
                'day'   => $date->format('d'),
                'count' => $dayLogs,
                'total' => $totalHabits,
                'level' => $totalHabits > 0 ? min(4, intdiv($dayLogs * 4, $totalHabits)) : 0,
            ];
        }

        return response()->json([
            'today_habits'      => $todayHabits,
            'completed_today'   => $completedToday,
            'total_habits'      => $totalHabits,
            'weekly_data'       => $weeklyData,
            'weekly_compliance' => $weeklyCompliance,
            'heatmap_data'      => $heatmapData,
        ]);
    }

    /**
     * POST /api/v/client/habits/toggle
     *
     * Toggle a habit for today. Ports HabitTracker.php toggleHabit().
     */
    public function toggleHabit(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'habit_type' => 'required|string|in:agua,sueno,entrenamiento,nutricion,suplementos',
        ]);

        $habitType = $request->input('habit_type');
        $today     = now()->toDateString();

        $log = HabitLog::where('client_id', $clientId)
            ->where('log_date', $today)
            ->where('habit_type', $habitType)
            ->first();

        if ($log) {
            $log->update(['value' => ! $log->value]);
            $completed = (bool) $log->value;
        } else {
            HabitLog::create([
                'client_id'  => $clientId,
                'log_date'   => $today,
                'habit_type' => $habitType,
                'value'      => true,
            ]);
            $completed = true;
        }

        // Check if all habits completed
        $completedCount = HabitLog::where('client_id', $clientId)
            ->where('log_date', $today)
            ->where('value', '>=', 1)
            ->count();

        $allCompleted = $completedCount >= 5;

        return response()->json([
            'completed'     => $completed,
            'all_completed' => $allCompleted,
        ]);
    }

    // ─── Referrals ─────────────────────────────────────────────────────

    /**
     * GET /api/v/client/referrals
     *
     * Referral program data. Ports ReferralProgram.php render().
     */
    public function referrals(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $referrals = Referral::where('referrer_id', $client->id)
            ->orderByDesc('created_at')
            ->get();

        $total     = $referrals->count();
        $converted = $referrals->where('status', 'converted')->count();
        $pending   = $referrals->where('status', 'pending')->count();
        $tasa      = $total > 0 ? round(($converted / $total) * 100, 1) : 0;

        $referralCode = base64_encode($client->id . ':' . substr(md5($client->id . $client->email), 0, 8));
        $referralLink = config('app.url') . '/inscripcion?ref=' . urlencode($referralCode);

        return response()->json([
            'referrals' => $referrals->map(fn ($r) => [
                'id'             => $r->id,
                'referred_email' => $r->referred_email,
                'status'         => $r->status,
                'reward_granted' => $r->reward_granted,
                'created_at'     => $r->created_at?->toIso8601String(),
            ])->toArray(),
            'stats' => [
                'total'     => $total,
                'converted' => $converted,
                'pending'   => $pending,
                'tasa'      => $tasa,
            ],
            'referral_link' => $referralLink,
            'referral_code' => $referralCode,
        ]);
    }

    /**
     * POST /api/v/client/referrals/invite
     *
     * Send referral invitation. Ports ReferralProgram.php sendInvite().
     */
    public function sendReferralInvite(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = $request->input('email');

        $existing = Referral::where('referrer_id', $client->id)
            ->where('referred_email', $email)
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Ya enviaste una invitacion a este correo.'], 422);
        }

        Referral::create([
            'referrer_id'    => $client->id,
            'referred_email' => $email,
            'status'         => 'pending',
            'reward_granted' => false,
            'created_at'     => now(),
        ]);

        $referralCode = base64_encode($client->id . ':' . substr(md5($client->id . $client->email), 0, 8));
        $referralLink = config('app.url') . '/inscripcion?ref=' . urlencode($referralCode);

        Mail::to($email)
            ->queue(new ReferralInvitation(
                referrerName: $client->name ?? 'Tu amigo en WellCore',
                referralLink: $referralLink,
            ));

        return response()->json([
            'sent'  => true,
            'email' => $email,
        ], 201);
    }

    // ─── Supplements ───────────────────────────────────────────────────

    /**
     * GET /api/v/client/supplements
     *
     * Supplement tracker weekly view. Ports SupplementTracker.php render().
     */
    public function supplements(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $selectedDate = $request->query('date', now()->toDateString());

        // Load plan
        $plan = AssignedPlan::where('client_id', $clientId)
            ->where('plan_type', 'suplementacion')
            ->where('active', true)
            ->latest()
            ->first();

        $supplementPlan = null;
        if ($plan && $plan->content) {
            $supplementPlan = is_array($plan->content)
                ? $plan->content
                : json_decode($plan->content, true);
        }

        $timingLabels = [
            'manana' => 'Manana',
            'tarde'  => 'Tarde',
            'noche'  => 'Noche',
            'pre'    => 'Pre-entreno',
            'post'   => 'Post-entreno',
        ];

        $supplements = [];

        if ($supplementPlan && isset($supplementPlan['suplementos'])) {
            $todayLogs = SupplementLog::where('client_id', $clientId)
                ->where('log_date', $selectedDate)
                ->get()
                ->groupBy(fn ($l) => $l->supplement_name . '|' . $l->timing);

            foreach ($supplementPlan['suplementos'] as $supp) {
                $name    = $supp['nombre'] ?? $supp['name'] ?? '';
                $dose    = $supp['dosis'] ?? $supp['dose'] ?? '';
                $timings = $supp['horarios'] ?? $supp['timing'] ?? ['manana'];
                $notes   = $supp['notas'] ?? $supp['notes'] ?? '';

                if (! is_array($timings)) {
                    $timings = [$timings];
                }

                $timingStatus = [];
                foreach ($timings as $t) {
                    $key = $name . '|' . $t;
                    $log = $todayLogs->get($key)?->first();
                    $timingStatus[] = [
                        'timing' => $t,
                        'label'  => $timingLabels[$t] ?? ucfirst($t),
                        'taken'  => $log && $log->taken,
                    ];
                }

                $allTaken = collect($timingStatus)->every(fn ($ts) => $ts['taken']);
                $anyTaken = collect($timingStatus)->contains(fn ($ts) => $ts['taken']);

                $supplements[] = [
                    'name'     => $name,
                    'dose'     => $dose,
                    'notes'    => $notes,
                    'timings'  => $timingStatus,
                    'allTaken' => $allTaken,
                    'anyTaken' => $anyTaken,
                ];
            }
        }

        // Weekly adherence
        $weekStart = Carbon::parse($selectedDate)->subDays(6);
        $weekLogs  = SupplementLog::where('client_id', $clientId)
            ->whereBetween('log_date', [$weekStart->toDateString(), $selectedDate])
            ->where('taken', true)
            ->get();

        $totalExpected = 0;
        $totalTaken    = 0;
        if ($supplementPlan && isset($supplementPlan['suplementos'])) {
            foreach ($supplementPlan['suplementos'] as $supp) {
                $timings = $supp['horarios'] ?? $supp['timing'] ?? ['manana'];
                if (! is_array($timings)) {
                    $timings = [$timings];
                }
                $totalExpected += count($timings) * 7;
            }
            $totalTaken = $weekLogs->count();
        }
        $weeklyAdherence = $totalExpected > 0 ? min(100, round(($totalTaken / $totalExpected) * 100)) : 0;

        // Daily adherence sparkline
        $dailyAdherence = [];
        for ($i = 6; $i >= 0; $i--) {
            $date    = Carbon::parse($selectedDate)->subDays($i);
            $dayLogs = $weekLogs->filter(fn ($l) => $l->log_date->toDateString() === $date->toDateString());
            $expectedPerDay = 0;
            if ($supplementPlan && isset($supplementPlan['suplementos'])) {
                foreach ($supplementPlan['suplementos'] as $supp) {
                    $timings = $supp['horarios'] ?? $supp['timing'] ?? ['manana'];
                    if (! is_array($timings)) {
                        $timings = [$timings];
                    }
                    $expectedPerDay += count($timings);
                }
            }
            $dailyAdherence[] = [
                'day'        => $date->locale('es')->isoFormat('dd'),
                'date'       => $date->format('d'),
                'taken'      => $dayLogs->count(),
                'expected'   => $expectedPerDay,
                'pct'        => $expectedPerDay > 0 ? min(100, round(($dayLogs->count() / $expectedPerDay) * 100)) : 0,
                'isSelected' => $date->format('Y-m-d') === $selectedDate,
            ];
        }

        $isToday        = $selectedDate === now()->toDateString();
        $totalToday     = count($supplements);
        $completedToday = collect($supplements)->where('allTaken', true)->count();

        return response()->json([
            'has_plan'         => $supplementPlan !== null,
            'supplements'      => $supplements,
            'selected_date'    => $selectedDate,
            'is_today'         => $isToday,
            'total_today'      => $totalToday,
            'completed_today'  => $completedToday,
            'weekly_adherence' => $weeklyAdherence,
            'daily_adherence'  => $dailyAdherence,
        ]);
    }

    /**
     * POST /api/v/client/supplements/toggle
     *
     * Toggle supplement taken status. Ports SupplementTracker.php toggleSupplement().
     */
    public function toggleSupplement(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'supplement_name' => 'required|string|max:255',
            'timing'          => 'required|string|max:50',
            'date'            => 'nullable|date|before_or_equal:today',
        ]);

        $selectedDate = $request->input('date', now()->toDateString());

        if ($selectedDate > today()->toDateString()) {
            return response()->json(['error' => 'No puedes registrar suplementos en fechas futuras.'], 422);
        }

        $supplementName = $request->input('supplement_name');
        $timing         = $request->input('timing');

        $log = SupplementLog::where('client_id', $clientId)
            ->where('log_date', $selectedDate)
            ->where('supplement_name', $supplementName)
            ->where('timing', $timing)
            ->first();

        if ($log) {
            $log->update(['taken' => ! $log->taken]);
            $taken = $log->taken;
        } else {
            SupplementLog::create([
                'client_id'       => $clientId,
                'log_date'        => $selectedDate,
                'supplement_name' => $supplementName,
                'timing'          => $timing,
                'taken'           => true,
            ]);
            $taken = true;
        }

        return response()->json(['taken' => $taken]);
    }

    // ─── Progress Photos ───────────────────────────────────────────────

    /**
     * GET /api/v/client/photos
     *
     * Progress photos grouped by date. Ports ProgressPhotos.php loadPhotos().
     */
    public function photos(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $photos = ProgressPhoto::where('client_id', $clientId)
            ->orderByDesc('photo_date')
            ->limit(60)
            ->get(['id', 'photo_date', 'tipo', 'filename'])
            ->groupBy(fn ($photo) => $photo->photo_date->format('Y-m-d'))
            ->map(fn ($group) => $group->map(fn ($p) => [
                'id'         => $p->id,
                'photo_date' => $p->photo_date->format('Y-m-d'),
                'tipo'       => $p->tipo,
                'filename'   => $p->filename,
                'url'        => Storage::disk('public')->url($p->filename),
            ])->toArray())
            ->toArray();

        return response()->json(['photos' => $photos]);
    }

    /**
     * POST /api/v/client/photos
     *
     * Upload progress photo. Ports ProgressPhotos.php uploadPhotos().
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'photo_date' => 'required|date',
            'tipo'       => 'required|in:frente,perfil,espalda',
            'photo'      => 'required|image|max:5120',
        ]);

        $photo = $request->file('photo');
        $uploadDate = $request->input('photo_date');
        $tipo = $request->input('tipo');

        $relativePath = sprintf(
            'progress/%d/%s_%s.%s',
            $clientId,
            $uploadDate,
            $tipo,
            $photo->getClientOriginalExtension()
        );

        $photo->storeAs(
            dirname($relativePath),
            basename($relativePath),
            'public'
        );

        $record = ProgressPhoto::create([
            'client_id'  => $clientId,
            'photo_date' => $uploadDate,
            'tipo'       => $tipo,
            'filename'   => $relativePath,
        ]);

        return response()->json([
            'id'       => $record->id,
            'url'      => Storage::disk('public')->url($relativePath),
            'filename' => $relativePath,
        ], 201);
    }

    /**
     * DELETE /api/v/client/photos/{id}
     *
     * Delete a progress photo.
     */
    public function deletePhoto(Request $request, int $id): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $photo = ProgressPhoto::where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (! $photo) {
            return response()->json(['error' => 'Foto no encontrada.'], 404);
        }

        // Delete file from storage
        if ($photo->filename && Storage::disk('public')->exists($photo->filename)) {
            Storage::disk('public')->delete($photo->filename);
        }

        $photo->delete();

        return response()->json(['deleted' => true]);
    }

    // ─── Personal Records ──────────────────────────────────────────────

    /**
     * GET /api/v/client/records
     *
     * Personal records. Ports PersonalRecords.php render().
     */
    public function records(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $category = $request->query('category', 'all');
        $search   = $request->query('search', '');

        $query = PersonalRecord::where('client_id', $clientId)
            ->orderByDesc('achieved_at')
            ->orderByDesc('id');

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        if (strlen($search) > 0) {
            $query->where('exercise', 'like', '%' . $search . '%');
        }

        $records    = $query->limit(500)->get();
        $currentPrs = $records->where('is_current', true)->keyBy('exercise');

        $hasFilters = $category !== 'all' || strlen($search) > 0;

        if ($hasFilters) {
            $totalPrs       = $records->where('is_current', true)->count();
            $totalExercises = $records->where('is_current', true)->unique('exercise')->count();
            $thisMonth      = $records->where('achieved_at', '>=', now()->startOfMonth())->count();
        } else {
            $stats = Cache::remember("pr:stats:{$clientId}", 60, function () use ($clientId) {
                $row = PersonalRecord::where('client_id', $clientId)
                    ->selectRaw(
                        'COUNT(DISTINCT exercise) as total_exercises,
                         SUM(CASE WHEN is_current = 1 THEN 1 ELSE 0 END) as total_prs,
                         SUM(CASE WHEN achieved_at >= ? THEN 1 ELSE 0 END) as this_month',
                        [now()->startOfMonth()]
                    )
                    ->first();

                return $row ? $row->toArray() : ['total_exercises' => 0, 'total_prs' => 0, 'this_month' => 0];
            });

            $totalPrs       = (int) ($stats['total_prs'] ?? 0);
            $totalExercises = (int) ($stats['total_exercises'] ?? 0);
            $thisMonth      = (int) ($stats['this_month'] ?? 0);
        }

        return response()->json([
            'records' => $records->map(fn ($r) => [
                'id'           => $r->id,
                'exercise'     => $r->exercise,
                'category'     => $r->category,
                'weight'       => $r->weight,
                'reps'         => $r->reps,
                'duration_sec' => $r->duration_sec,
                'distance_km'  => $r->distance_km,
                'notes'        => $r->notes,
                'achieved_at'  => $r->achieved_at?->format('Y-m-d'),
                'is_current'   => (bool) $r->is_current,
            ])->toArray(),
            'current_prs' => $currentPrs->map(fn ($r) => [
                'id'       => $r->id,
                'exercise' => $r->exercise,
                'weight'   => $r->weight,
                'reps'     => $r->reps,
            ])->toArray(),
            'stats' => [
                'total_prs'       => $totalPrs,
                'total_exercises' => $totalExercises,
                'this_month'      => $thisMonth,
            ],
        ]);
    }

    // ─── AI Nutrition ──────────────────────────────────────────────────

    /**
     * GET /api/v/client/ai-nutrition
     *
     * Returns the AI nutrition analysis history. Stateless — no DB persistence.
     */
    public function aiNutritionHistory(Request $request): JsonResponse
    {
        return response()->json(['history' => []]);
    }

    /**
     * POST /api/v/client/ai-nutrition/analyze
     *
     * AI nutrition analysis from an uploaded image. Ports AINutrition.php analyzePhoto().
     */
    public function aiNutritionAnalyze(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $apiKey      = config('wellcore.ai.api_key', '');
        $aiAvailable = str_starts_with($apiKey, 'sk-ant') || strlen($apiKey) > 20;

        if (! $aiAvailable) {
            return response()->json(['error' => 'El analisis AI no esta disponible.'], 503);
        }

        try {
            $photo    = $request->file('photo');
            $path     = $photo->getRealPath();
            $base64   = base64_encode(file_get_contents($path));
            $mimeType = $photo->getMimeType();

            $aiService = app(AIService::class);

            $systemPrompt = 'Eres un nutricionista deportivo experto con 20 anos de experiencia analizando comidas para atletas. Tu especialidad es estimar macronutrientes con alta precision basandote en el tamano visual de las porciones, la densidad de los alimentos y las preparaciones culinarias tipicas de Latinoamerica. Siempre devuelves UNICAMENTE JSON valido, sin texto adicional ni markdown.';

            $userMessage = 'Analiza con detalle esta imagen de comida para un atleta de fitness. Identifica cada componente del plato y estima los macronutrientes para la porcion visible.

Responde EXACTAMENTE con este JSON (sin texto adicional):
{
  "food_name": "nombre descriptivo del plato completo",
  "ingredients": [
    {"name": "ingrediente", "grams": 150, "calories": 165, "protein_g": 31, "carbs_g": 0, "fat_g": 3.6}
  ],
  "calories": 500,
  "protein_g": 45.0,
  "carbs_g": 35.0,
  "fat_g": 15.0,
  "fiber_g": 5.0,
  "confidence": "high",
  "confidence_reason": "breve explicacion de la confianza",
  "notes": "observaciones relevantes para el atleta",
  "meal_type": "desayuno|almuerzo|cena|merienda|snack"
}';

            $rawResponse = $aiService->analyzeImage($base64, $mimeType, $systemPrompt, $userMessage, 1024);

            if (! $rawResponse) {
                return response()->json(['error' => 'No se pudo analizar la imagen. Intenta de nuevo.'], 422);
            }

            preg_match('/\{.*\}/s', $rawResponse, $matches);
            $json = $matches[0] ?? $rawResponse;
            $data = json_decode($json, true);

            if (! $data || ! isset($data['calories'])) {
                return response()->json(['error' => 'No se pudo interpretar el analisis.'], 422);
            }

            $data['fiber_g']           = isset($data['fiber_g']) ? (float) $data['fiber_g'] : null;
            $data['ingredients']       = isset($data['ingredients']) && is_array($data['ingredients']) ? $data['ingredients'] : [];
            $data['confidence_reason'] = $data['confidence_reason'] ?? '';
            $data['meal_type']         = $data['meal_type'] ?? '';

            return response()->json([
                'analysis' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('AI Nutrition API error', ['message' => $e->getMessage()]);

            return response()->json(['error' => 'Error al procesar la imagen.'], 500);
        }
    }

    // ─── Video Check-ins ──────────────────────────────────────────────

    /**
     * GET /api/v/client/video-checkins
     *
     * Returns the client's video check-in upload history and monthly usage.
     */
    public function videoCheckinHistory(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $checkins = VideoCheckin::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $monthlyCount = VideoCheckin::where('client_id', $clientId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return response()->json([
            'checkins'      => $checkins->map(fn ($c) => [
                'id'             => $c->id,
                'media_type'     => $c->media_type,
                'media_url'      => $c->media_url,
                'media_full_url' => asset('storage/' . $c->media_url),
                'exercise_name'  => $c->exercise_name,
                'notes'          => $c->notes,
                'status'         => $c->status,
                'coach_response' => $c->coach_response ?? null,
                'ai_response'    => $c->ai_response ?? null,
                'ai_used'        => (bool) $c->ai_used,
                'created_at'     => $c->created_at?->toIso8601String(),
                'created_at_human' => $c->created_at?->diffForHumans(),
            ])->toArray(),
            'monthly_count' => $monthlyCount,
            'monthly_limit' => 4,
        ]);
    }

    /**
     * POST /api/v/client/video-checkin
     *
     * Handle multipart file upload for video or image check-in.
     */
    public function videoCheckinSubmit(Request $request): JsonResponse
    {
        $client   = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        // Detect media type from extension before validation so we can apply the
        // correct max-size rule.
        $file      = $request->file('media_file');
        $extension = $file ? strtolower($file->getClientOriginalExtension()) : '';
        $isImage   = in_array($extension, ['jpg', 'jpeg', 'png'], true);
        $mediaType = $isImage ? 'image' : 'video';
        $maxKb     = $isImage ? 10240 : 102400; // 10 MB image / 100 MB video

        $request->validate([
            'media_file'    => "required|file|mimes:mp4,mov,webm,jpg,jpeg,png|max:{$maxKb}",
            'exercise_name' => 'required|string|max:200',
            'notes'         => 'nullable|string|max:2000',
        ]);

        // Enforce monthly limit of 4.
        $monthlyCount = VideoCheckin::where('client_id', $clientId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        if ($monthlyCount >= 4) {
            return response()->json([
                'errors' => [
                    'media_file' => ['Has alcanzado el límite de 4 video check-ins este mes.'],
                ],
            ], 422);
        }

        $storedPath = $request->file('media_file')->store('checkins/' . $clientId, 'public');

        $coachId = AssignedPlan::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->value('assigned_by');

        $checkin = VideoCheckin::create([
            'client_id'           => $clientId,
            'coach_id'            => $coachId,
            'media_type'          => $mediaType,
            'media_url'           => $storedPath,
            'exercise_name'       => trim($request->input('exercise_name')),
            'notes'               => $request->input('notes') ? trim($request->input('notes')) : null,
            'status'              => 'pending',
            'ai_used'             => false,
            'plan_uses_this_month' => $monthlyCount + 1,
            'created_at'          => now(),
        ]);

        return response()->json([
            'message' => 'Check-in enviado correctamente.',
            'id'      => $checkin->id,
        ], 201);
    }

    // ─── Private helpers ───────────────────────────────────────────────

    /**
     * Parse macros from nutrition plan. Ported from NutritionPlan.php.
     */
    private function parseMacros(?array $plan): array
    {
        if (! $plan) {
            return ['protein_g' => 0, 'carb_g' => 0, 'fat_g' => 0, 'total_calories' => 0, 'has_macros' => false, 'percentages' => ['protein' => 0, 'carbs' => 0, 'fat' => 0]];
        }

        $macros     = $plan['macros'] ?? [];
        $day0macros = $plan['dias'][0]['macros'] ?? [];

        $proteinGrams = (int) ($macros['proteina_g'] ?? $macros['proteina'] ?? $macros['protein_g'] ?? $macros['protein'] ?? $macros['proteina_g_dia']
            ?? $day0macros['proteina_g'] ?? $day0macros['proteina'] ?? $day0macros['protein']
            ?? $plan['proteina_g'] ?? 0);
        $carbGrams = (int) ($macros['carbohidratos_g'] ?? $macros['carbs_g'] ?? $macros['carbohidratos'] ?? $macros['carbs']
            ?? $day0macros['carbohidratos_g'] ?? $day0macros['carbs_g'] ?? $day0macros['carbohidratos'] ?? $day0macros['carbs']
            ?? $plan['carbohidratos_g'] ?? 0);
        $fatGrams = (int) ($macros['grasas_g'] ?? $macros['grasa_g'] ?? $macros['grasas'] ?? $macros['fat_g'] ?? $macros['fat']
            ?? $day0macros['grasas_g'] ?? $day0macros['grasa_g'] ?? $day0macros['grasas'] ?? $day0macros['fat']
            ?? $plan['grasas_g'] ?? $plan['grasa_g'] ?? 0);

        $hasMacros = ($proteinGrams + $carbGrams + $fatGrams) > 0;

        $planCalories = (int) ($plan['calorias_diarias'] ?? $plan['calorias'] ?? $plan['objetivo_cal']
            ?? $plan['calorias_objetivo'] ?? $plan['calories_target'] ?? $macros['calorias']
            ?? ($plan['dias'][0]['kcal_total'] ?? 0));

        $totalCalories = $planCalories > 0
            ? $planCalories
            : ($proteinGrams * 4) + ($carbGrams * 4) + ($fatGrams * 9);

        $total = $proteinGrams + $carbGrams + $fatGrams;
        $percentages = $total > 0 ? [
            'protein' => (int) round(($proteinGrams / $total) * 100),
            'carbs'   => (int) round(($carbGrams / $total) * 100),
            'fat'     => (int) round(($fatGrams / $total) * 100),
        ] : ['protein' => 0, 'carbs' => 0, 'fat' => 0];

        return [
            'protein_g'      => $proteinGrams,
            'carb_g'         => $carbGrams,
            'fat_g'          => $fatGrams,
            'total_calories' => $totalCalories,
            'has_macros'     => $hasMacros,
            'percentages'    => $percentages,
        ];
    }

    /**
     * Parse meals from nutrition plan. Ported from NutritionPlan.php.
     */
    private function parseMeals(?array $plan): array
    {
        if (! $plan) {
            return [];
        }

        $diasComidas = null;
        if (isset($plan['dias']) && is_array($plan['dias'])) {
            foreach ($plan['dias'] as $dia) {
                if (! empty($dia['comidas'])) {
                    $diasComidas = $dia['comidas'];
                    break;
                }
            }
        }

        $planSemanalComidas = null;
        if (isset($plan['plan_semanal']) && is_array($plan['plan_semanal'])) {
            foreach ($plan['plan_semanal'] as $dia) {
                if (! empty($dia['comidas'])) {
                    $planSemanalComidas = $dia['comidas'];
                    break;
                }
            }
        }

        $raw = $plan['comidas']
            ?? $plan['plan_dia_entrenamiento']['comidas']
            ?? $plan['meals']
            ?? $diasComidas
            ?? $planSemanalComidas
            ?? [];

        return array_map(function (array $meal) {
            $macros = $meal['macros'] ?? [];

            return [
                'nombre'    => $meal['nombre'] ?? $meal['name'] ?? $meal['label'] ?? 'Comida',
                'calorias'  => (int) ($meal['calorias'] ?? $meal['calories'] ?? $meal['kcal'] ?? $meal['cal'] ?? 0),
                'alimentos' => $meal['alimentos'] ?? $meal['foods'] ?? $meal['items'] ?? [],
                'notas'     => $meal['notas'] ?? $meal['notes'] ?? null,
                'macros'    => [
                    'proteina'      => (int) ($macros['proteina_g'] ?? $macros['proteina'] ?? $macros['protein_g'] ?? $macros['protein'] ?? 0),
                    'carbohidratos' => (int) ($macros['carbs_g'] ?? $macros['carbohidratos_g'] ?? $macros['carbohidratos'] ?? $macros['carbs'] ?? 0),
                    'grasas'        => (int) ($macros['grasas_g'] ?? $macros['grasa_g'] ?? $macros['grasas'] ?? $macros['fat_g'] ?? $macros['fat'] ?? 0),
                ],
            ];
        }, $raw);
    }

    /**
     * Parse extras from nutrition plan. Ported from NutritionPlan.php.
     */
    private function parseExtras(?array $plan): array
    {
        if (! $plan) {
            return [];
        }

        $extras = [
            'coach_notes'      => $plan['notas_coach'] ?? $plan['coach_notes'] ?? null,
            'objetivo'         => $plan['objetivo'] ?? $plan['objetivo_general'] ?? null,
            'tips'             => is_array($plan['tips'] ?? null) ? $plan['tips'] : (is_array($plan['tips_nutricionales'] ?? null) ? $plan['tips_nutricionales'] : []),
            'comidas_sugeridas' => is_array($plan['comidas_sugeridas'] ?? null) ? $plan['comidas_sugeridas'] : [],
            'rest_day_info'    => null,
            'hydration_note'   => null,
        ];

        if (isset($plan['plan_dia_descanso'])) {
            $rest = $plan['plan_dia_descanso'];
            $extras['rest_day_info'] = [
                'descripcion'       => $rest['descripcion'] ?? null,
                'calorias_objetivo' => (int) ($rest['calorias_objetivo'] ?? 0),
                'ajustes'           => $rest['ajustes'] ?? [],
            ];
        }

        if (isset($plan['hidratacion'])) {
            $extras['hydration_note'] = $plan['hidratacion']['electrolitos'] ?? null;
        }

        return $extras;
    }

    /**
     * Load water tracking data. Ported from NutritionPlan.php.
     */
    private function loadWaterData(int $clientId, ?array $plan): array
    {
        $waterGoalMl = 2500;

        if ($plan && isset($plan['hidratacion'])) {
            $liters = (float) ($plan['hidratacion']['agua_minima_litros'] ?? 0);
            if ($liters > 0) {
                $waterGoalMl = (int) ($liters * 1000);
            }
        }

        $todayWater = HabitLog::where('client_id', $clientId)
            ->where('habit_type', 'agua')
            ->where('log_date', now()->toDateString())
            ->first();

        return [
            'goal_ml'     => $waterGoalMl,
            'consumed_ml' => $todayWater ? (int) $todayWater->value : 0,
        ];
    }

    // ─── Coach Video Tips ──────────────────────────────────────────────

    /**
     * GET /api/v/client/videos
     *
     * Coach video tips filtered by the client's assigned coach.
     */
    public function videos(Request $request): JsonResponse
    {
        $client = auth('wellcore')->user();

        if (! $client) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $coachId = AssignedPlan::where('client_id', $client->id)
            ->whereNotNull('assigned_by')
            ->latest('valid_from')
            ->value('assigned_by');

        $query = CoachVideoTip::where('is_active', true)
            ->orderBy('sort_order');

        if ($coachId) {
            $query->where(function ($q) use ($coachId) {
                $q->where('coach_id', $coachId)->orWhereNull('coach_id');
            });
        }

        if ($search = $request->query('search')) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        $videos = $query->limit(50)->get(['id', 'title', 'video_url', 'thumbnail_url', 'duration_sec']);

        return response()->json(['videos' => $videos]);
    }

    // ─── Academy Content ───────────────────────────────────────────────

    /**
     * GET /api/v/client/academia
     *
     * Academy content library (public catalog, optional auth).
     */
    public function academia(Request $request): JsonResponse
    {
        $query = AcademyContent::where('active', true)
            ->orderBy('sort_order');

        if ($search = $request->query('search')) {
            $query->where(fn ($q) => $q
                ->where('title', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%')
            );
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $contents = $query->limit(100)->get([
            'id', 'title', 'category', 'description',
            'content_type', 'thumbnail_url', 'content_url',
            'body_html', 'sort_order',
        ]);

        $categories = AcademyContent::where('active', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json([
            'contents'   => $contents,
            'categories' => $categories,
        ]);
    }

    /**
     * Load weight tracking data. Ported from NutritionPlan.php.
     */
    private function loadWeightData(int $clientId, ?array $plan): array
    {
        $profile = ClientProfile::where('client_id', $clientId)->first();

        $weightGoalKg   = null;
        $currentWeightKg = null;

        if ($plan && isset($plan['peso_objetivo'])) {
            $weightGoalKg = (float) $plan['peso_objetivo'];
        } elseif ($profile && is_numeric($profile->objetivo ?? null)) {
            $weightGoalKg = (float) $profile->objetivo;
        }

        $latest = BiometricLog::where('client_id', $clientId)
            ->whereNotNull('weight_kg')
            ->where('weight_kg', '>', 0)
            ->latest('log_date')
            ->first();

        if ($latest) {
            $currentWeightKg = (float) $latest->weight_kg;
        } elseif ($profile && $profile->peso) {
            $currentWeightKg = (float) $profile->peso;
        }

        return [
            'goal_kg'    => $weightGoalKg,
            'current_kg' => $currentWeightKg,
        ];
    }
}
