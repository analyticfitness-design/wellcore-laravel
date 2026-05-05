<?php

namespace App\Services;

use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostComment;
use App\Models\PostMention;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CoachCommunityService
{
    /**
     * Resolve client IDs assigned to coach via 3-fallback (matches GroupPulseAggregator).
     *
     * @return array<int, int>
     */
    public function resolveClientIds(int $coachId): array
    {
        $primary = Client::where('coach_id', $coachId)->pluck('id')->all();

        $fallbackPlans = Schema::hasTable('assigned_plans')
            ? DB::table('assigned_plans')->where('assigned_by', $coachId)->pluck('client_id')->all()
            : [];

        $fallbackMessages = Schema::hasTable('coach_messages')
            ? DB::table('coach_messages')->where('coach_id', $coachId)->pluck('client_id')->all()
            : [];

        return array_values(array_unique(array_merge($primary, $fallbackPlans, $fallbackMessages)));
    }

    public function getFeed(int $coachId, string $filter = 'all', int $perPage = 20): array
    {
        $clientIds = $this->resolveClientIds($coachId);

        $q = CommunityPost::query()
            ->where(function ($q) use ($clientIds, $coachId) {
                $q->whereIn('client_id', $clientIds)
                    ->orWhere('coach_admin_id', $coachId);
            })
            ->where('visible', true)
            ->orderByDesc('created_at');

        if ($filter === 'pinned') {
            $q->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('pinned_posts')
                    ->whereColumn('pinned_posts.post_id', 'community_posts.id')
                    ->where(function ($w) {
                        $w->whereNull('pinned_until')->orWhere('pinned_until', '>', now());
                    });
            });
        }

        if ($filter === 'reported') {
            $q->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('post_reports')
                    ->whereColumn('post_reports.post_id', 'community_posts.id')
                    ->where('status', 'pending');
            });
        }

        if ($filter === 'achievements') {
            $q->whereIn('post_type', ['achievement', 'pr', 'milestone']);
        }

        if ($filter === 'prs') {
            $q->where('post_type', 'pr');
        }

        $page = $q->paginate($perPage);

        return [
            'data' => $page->items(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'total' => $page->total(),
        ];
    }

    /**
     * @return array<int, array{client_id:int, workout_count:int}>
     */
    public function topPerformers(int $coachId, int $days = 7, int $limit = 3): array
    {
        $clientIds = $this->resolveClientIds($coachId);

        if (empty($clientIds)) {
            return [];
        }

        $rows = WorkoutSession::query()
            ->select('client_id', DB::raw('COUNT(*) as workout_count'))
            ->whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->where('session_date', '>=', Carbon::today()->subDays($days))
            ->groupBy('client_id')
            ->orderByDesc('workout_count')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            'client_id' => (int) $r->client_id,
            'workout_count' => (int) $r->workout_count,
        ])->all();
    }

    /**
     * @return array<int, Client>
     */
    public function atRiskClients(int $coachId, int $days = 5): array
    {
        $clientIds = $this->resolveClientIds($coachId);

        if (empty($clientIds)) {
            return [];
        }

        $activeIds = WorkoutSession::query()
            ->whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->where('session_date', '>=', Carbon::today()->subDays($days))
            ->pluck('client_id')
            ->unique()
            ->all();

        $silentIds = array_diff($clientIds, $activeIds);

        if (empty($silentIds)) {
            return [];
        }

        $columns = Schema::hasColumn('clients', 'last_login_at')
            ? ['id', 'name', 'last_login_at']
            : ['id', 'name'];

        return Client::whereIn('id', $silentIds)
            ->select($columns)
            ->get()
            ->all();
    }

    public function teamHealthScore(int $coachId): float
    {
        $clientIds = $this->resolveClientIds($coachId);

        if (empty($clientIds)) {
            return 0.0;
        }

        $activeCount = WorkoutSession::query()
            ->whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->where('session_date', '>=', Carbon::today()->subDays(7))
            ->distinct('client_id')
            ->count('client_id');

        return round(($activeCount / count($clientIds)) * 100, 1);
    }

    /**
     * Threads activos: posts del coach con comentarios últimos $sinceDays días.
     *
     * @return array{data: array, pagination: array}
     */
    public function threads(int $coachId, int $sinceDays = 7, int $page = 1, int $perPage = 20): array
    {
        $clientIds = $this->resolveClientIds($coachId);

        if (empty($clientIds)) {
            return [
                'data' => [],
                'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            ];
        }

        $since = now()->subDays($sinceDays);

        $query = CommunityPost::query()
            ->whereIn('client_id', $clientIds)
            ->where('visible', true)
            ->whereExists(function ($q) use ($since) {
                $q->select(DB::raw(1))
                    ->from('post_comments')
                    ->whereColumn('post_comments.post_id', 'community_posts.id')
                    ->where('post_comments.created_at', '>=', $since);
            })
            ->withCount(['comments' => fn ($q) => $q->where('created_at', '>=', $since)])
            ->orderByDesc(
                DB::raw('(select max(created_at) from post_comments where post_comments.post_id = community_posts.id)')
            );

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $data = $paginator->getCollection()->map(function (CommunityPost $post) use ($coachId, $since) {
            $hasCoachReply = PostComment::query()
                ->where('post_id', $post->id)
                ->where('author_type', 'coach')
                ->where('author_admin_id', $coachId)
                ->exists();

            $participantsCount = PostComment::query()
                ->where('post_id', $post->id)
                ->where('created_at', '>=', $since)
                ->distinct('client_id')
                ->count('client_id');

            $isConflicted = $post->comments_count >= 10
                && PostMention::query()
                    ->where('post_id', $post->id)
                    ->where('mentioned_type', 'admin')
                    ->exists();

            $excerpt = mb_substr(strip_tags($post->content ?? ''), 0, 80);

            return [
                'post_id' => $post->id,
                'post_excerpt' => $excerpt,
                'post_author_name' => optional(Client::find($post->client_id))->name ?? 'Cliente',
                'thread_size' => $post->comments_count,
                'participants_count' => $participantsCount,
                'has_coach_reply' => $hasCoachReply,
                'is_conflicted' => $isConflicted,
                'last_activity_at' => optional(
                    PostComment::where('post_id', $post->id)->latest('created_at')->first()
                )->created_at?->toIso8601String(),
            ];
        })->all();

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /**
     * Achievements + PRs últimos $period del equipo.
     *
     * @param  string  $period  'week'|'month'|'all'
     * @return array{data: array, totals: array, pagination: array}
     */
    public function achievements(int $coachId, string $period = 'week', int $page = 1, int $perPage = 20): array
    {
        $clientIds = $this->resolveClientIds($coachId);

        if (empty($clientIds)) {
            return [
                'data' => [],
                'totals' => ['prs' => 0, 'achievements' => 0],
                'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            ];
        }

        $since = match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            default => null,
        };

        $prsQuery = Schema::hasTable('personal_records')
            ? DB::table('personal_records')->whereIn('client_id', $clientIds)
            : null;

        $prs = collect();
        if ($prsQuery) {
            if ($since) {
                $prsQuery->where('created_at', '>=', $since);
            }
            $prs = $prsQuery
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(function ($pr) {
                    $client = Client::find($pr->client_id);

                    return [
                        'type' => 'pr',
                        'client_id' => $pr->client_id,
                        'client_name' => $client?->name ?? 'Cliente',
                        'avatar_url' => $client?->avatar_url ?? null,
                        'exercise' => $pr->exercise ?? null,
                        'weight_kg' => $pr->weight ?? null,
                        'previous_weight_kg' => null,
                        'achieved_at' => $pr->created_at,
                    ];
                });
        }

        $achievements = collect();
        if (Schema::hasTable('client_achievements')) {
            $achievementsQuery = DB::table('client_achievements')->whereIn('client_id', $clientIds);
            if ($since) {
                $achievementsQuery->where('created_at', '>=', $since);
            }
            $achievements = $achievementsQuery
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(function ($a) {
                    $client = Client::find($a->client_id);

                    return [
                        'type' => 'achievement',
                        'client_id' => $a->client_id,
                        'client_name' => $client?->name ?? 'Cliente',
                        'avatar_url' => $client?->avatar_url ?? null,
                        'achievement_name' => $a->achievement_name ?? $a->name ?? 'Logro',
                        'achieved_at' => $a->created_at,
                    ];
                });
        }

        $merged = $prs->concat($achievements)
            ->sortByDesc('achieved_at')
            ->values();

        $offset = ($page - 1) * $perPage;
        $pageItems = $merged->slice($offset, $perPage)->values()->all();

        return [
            'data' => $pageItems,
            'totals' => [
                'prs' => $prs->count(),
                'achievements' => $achievements->count(),
            ],
            'pagination' => [
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($merged->count() / $perPage)),
                'total' => $merged->count(),
            ],
        ];
    }
}
