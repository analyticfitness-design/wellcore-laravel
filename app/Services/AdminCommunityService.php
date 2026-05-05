<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\BroadcastMessage;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\ModerationAction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminCommunityService
{
    /**
     * @return array<int, array{coach_id:int, coach_name:string, posts_count:int, reactions_count:int, engagement_rate:float}>
     */
    public function coachMetrics(string $period = 'week'): array
    {
        $since = match ($period) {
            'day' => Carbon::now()->subDay(),
            'month' => Carbon::now()->subMonth(),
            default => Carbon::now()->subWeek(),
        };

        $coaches = Admin::where('role', 'coach')->select(['id', 'name'])->get();

        $postsCounts = CommunityPost::query()
            ->where('created_at', '>=', $since)
            ->select('coach_admin_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('coach_admin_id')
            ->pluck('cnt', 'coach_admin_id');

        $reactionsCounts = DB::table('post_reactions')
            ->join('community_posts', 'post_reactions.post_id', '=', 'community_posts.id')
            ->where('community_posts.created_at', '>=', $since)
            ->select('community_posts.coach_admin_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('community_posts.coach_admin_id')
            ->pluck('cnt', 'coach_admin_id');

        return $coaches->map(function ($coach) use ($postsCounts, $reactionsCounts) {
            $posts = (int) ($postsCounts[$coach->id] ?? 0);
            $reactions = (int) ($reactionsCounts[$coach->id] ?? 0);

            return [
                'coach_id' => $coach->id,
                'coach_name' => $coach->name,
                'posts_count' => $posts,
                'reactions_count' => $reactions,
                'engagement_rate' => $posts > 0 ? round($reactions / $posts, 2) : 0,
            ];
        })->all();
    }

    /**
     * @return array<int, array{date:string, count:int}>
     */
    public function postsTimeSeries(int $days = 30): array
    {
        $start = Carbon::today()->subDays($days - 1);

        $rows = CommunityPost::query()
            ->where('created_at', '>=', $start)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $series[] = ['date' => $date, 'count' => (int) ($rows[$date] ?? 0)];
        }

        return $series;
    }

    public function moderationQueueCount(): int
    {
        return DB::table('post_reports')->where('status', 'pending')->count();
    }

    /**
     * Coach analytics drill-down: 90-day metrics, KPIs, top clients, alerts, audit.
     *
     * @return array{coach: array, kpis: array, posts_per_day_90d: array, engagement_per_day_90d: array, top_clients: array, alerts: array, recent_audit: array}
     */
    public function coachAnalytics(int $coachId): array
    {
        $coach = Admin::findOrFail($coachId);
        $clientIds = app(CoachCommunityService::class)->resolveClientIds($coachId);

        return [
            'coach' => [
                'id' => $coach->id,
                'name' => $coach->name,
                'avatar_url' => $coach->avatar_url ?? null,
                'joined_at' => $coach->created_at?->toIso8601String(),
                'role' => $coach->role instanceof \BackedEnum ? $coach->role->value : (string) $coach->role,
            ],
            'kpis' => [
                'active_clients' => Client::whereIn('id', $clientIds)->where('status', 'activo')->count(),
                'total_posts_30d' => CommunityPost::whereIn('client_id', $clientIds)
                    ->where('created_at', '>=', now()->subDays(30))->count(),
                'engagement_rate' => $this->engagementRate30d($clientIds),
                'response_time_p50_min' => $this->responseTimePercentile($coachId, $clientIds, 50),
                'response_time_p95_min' => $this->responseTimePercentile($coachId, $clientIds, 95),
                'moderation_actions_30d' => ModerationAction::query()
                    ->where('actor_type', 'coach')->where('actor_id', $coachId)
                    ->where('created_at', '>=', now()->subDays(30))->count(),
                'broadcasts_sent_30d' => BroadcastMessage::query()
                    ->where('sender_type', 'coach')->where('sender_id', $coachId)
                    ->where('sent_at', '>=', now()->subDays(30))->count(),
            ],
            'posts_per_day_90d' => $this->seriesPostsPerDay($clientIds, 90),
            'engagement_per_day_90d' => $this->seriesEngagementPerDay($clientIds, 90),
            'top_clients' => $this->topClientsForCoach($clientIds, 30),
            'alerts' => $this->coachAlerts($coachId, $clientIds),
            'recent_audit' => ModerationAction::query()
                ->where('actor_type', 'coach')->where('actor_id', $coachId)
                ->orderByDesc('created_at')->limit(10)->get()->toArray(),
        ];
    }

    private function engagementRate30d(array $clientIds): float
    {
        if (empty($clientIds)) return 0.0;
        $posts = CommunityPost::whereIn('client_id', $clientIds)
            ->where('created_at', '>=', now()->subDays(30))->count();
        if ($posts === 0) return 0.0;

        $reactions = DB::table('post_reactions')
            ->whereIn('post_id', function ($q) use ($clientIds) {
                $q->select('id')->from('community_posts')
                    ->whereIn('client_id', $clientIds)
                    ->where('created_at', '>=', now()->subDays(30));
            })->count();

        $comments = DB::table('post_comments')
            ->whereIn('post_id', function ($q) use ($clientIds) {
                $q->select('id')->from('community_posts')
                    ->whereIn('client_id', $clientIds)
                    ->where('created_at', '>=', now()->subDays(30));
            })->count();

        return round(($reactions + $comments) / max(1, $posts), 2);
    }

    private function responseTimePercentile(int $coachId, array $clientIds, int $percentile): int
    {
        if (empty($clientIds)) return 0;

        // Find first coach response per post in last 30 days
        $rows = DB::table('community_posts as p')
            ->join('post_comments as c', function ($join) use ($coachId) {
                $join->on('c.post_id', '=', 'p.id')
                    ->where('c.author_type', '=', 'coach')
                    ->where('c.author_admin_id', '=', $coachId);
            })
            ->whereIn('p.client_id', $clientIds)
            ->where('p.created_at', '>=', now()->subDays(30))
            ->select('p.id', 'p.created_at as post_at', DB::raw('MIN(c.created_at) as first_reply_at'))
            ->groupBy('p.id', 'p.created_at')
            ->get();

        $values = $rows->map(function ($r) {
            $postAt = strtotime($r->post_at);
            $replyAt = strtotime($r->first_reply_at);
            return max(0, (int) (($replyAt - $postAt) / 60));
        })->sort()->values()->all();

        if (empty($values)) return 0;

        $idx = (int) ceil(($percentile / 100) * count($values)) - 1;
        return $values[max(0, $idx)] ?? 0;
    }

    private function seriesPostsPerDay(array $clientIds, int $days): array
    {
        $start = now()->subDays($days)->startOfDay();
        $rows = DB::table('community_posts')
            ->whereIn('client_id', $clientIds ?: [0])
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')->get()->keyBy('d');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $series[] = ['date' => $date, 'count' => (int) ($rows[$date]->c ?? 0)];
        }
        return $series;
    }

    private function seriesEngagementPerDay(array $clientIds, int $days): array
    {
        $start = now()->subDays($days)->startOfDay();

        $reactionsRows = DB::table('post_reactions as r')
            ->join('community_posts as p', 'p.id', '=', 'r.post_id')
            ->whereIn('p.client_id', $clientIds ?: [0])
            ->where('r.created_at', '>=', $start)
            ->selectRaw('DATE(r.created_at) as d, COUNT(*) as c')
            ->groupBy('d')->get()->keyBy('d');

        $commentsRows = DB::table('post_comments as c')
            ->join('community_posts as p', 'p.id', '=', 'c.post_id')
            ->whereIn('p.client_id', $clientIds ?: [0])
            ->where('c.created_at', '>=', $start)
            ->selectRaw('DATE(c.created_at) as d, COUNT(*) as c')
            ->groupBy('d')->get()->keyBy('d');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $r = (int) ($reactionsRows[$date]->c ?? 0);
            $c = (int) ($commentsRows[$date]->c ?? 0);
            $series[] = ['date' => $date, 'count' => $r + $c];
        }
        return $series;
    }

    private function topClientsForCoach(array $clientIds, int $days): array
    {
        if (empty($clientIds)) return [];
        return DB::table('community_posts')
            ->whereIn('client_id', $clientIds)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('client_id, COUNT(*) as posts')
            ->groupBy('client_id')
            ->orderByDesc('posts')
            ->limit(5)
            ->get()
            ->map(function ($r) {
                $client = Client::find($r->client_id);
                return [
                    'client_id' => $r->client_id,
                    'client_name' => $client?->name ?? 'Cliente',
                    'posts' => (int) $r->posts,
                    'engagement_received' => 0,
                ];
            })
            ->all();
    }

    private function coachAlerts(int $coachId, array $clientIds): array
    {
        $alerts = [];

        foreach ($clientIds as $cid) {
            $client = Client::find($cid);
            if (! $client) continue;
            $lastLogin = $client->last_login_at ?? null;
            if ($lastLogin) {
                $lastLoginDate = $lastLogin instanceof Carbon ? $lastLogin : Carbon::parse($lastLogin);
                if ($lastLoginDate->lt(now()->subDays(7))) {
                    $alerts[] = [
                        'type' => 'client_inactive',
                        'client_id' => $cid,
                        'client_name' => $client->name,
                        'days' => $lastLoginDate->diffInDays(now()),
                    ];
                }
            }
        }

        return array_slice($alerts, 0, 10);
    }
}
