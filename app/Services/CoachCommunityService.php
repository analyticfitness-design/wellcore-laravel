<?php

namespace App\Services;

use App\Models\Client;
use App\Models\CommunityPost;
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
}
