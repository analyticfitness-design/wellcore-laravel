<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\CommunityPost;
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
}
