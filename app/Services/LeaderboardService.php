<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    public static function getGlobal(int $limit = 20): array
    {
        return Cache::remember('leaderboard:global', now()->addMinutes(10), function () use ($limit) {
            return DB::table('clients')
                ->select('id', 'name', 'avatar_url', 'xp_total', 'level', 'wellcoins_balance')
                ->where('status', 'active')
                ->orderByDesc('xp_total')
                ->limit($limit)
                ->get()
                ->map(function ($client, $index) {
                    $client->rank = $index + 1;
                    $client->tier = WellCoinsService::getTier($client->wellcoins_balance ?? 0);
                    return $client;
                })
                ->toArray();
        });
    }

    public static function getStreakLeaders(int $limit = 10): array
    {
        return Cache::remember('leaderboard:streaks', now()->addMinutes(10), function () use ($limit) {
            return DB::table('clients')
                ->select('id', 'name', 'avatar_url', 'streak_days')
                ->where('status', 'active')
                ->where('streak_days', '>', 0)
                ->orderByDesc('streak_days')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    public static function getMonthlyTop(int $limit = 10): array
    {
        return Cache::remember('leaderboard:monthly', now()->addMinutes(30), function () use ($limit) {
            return DB::table('wellcoins_transactions')
                ->select('client_id', DB::raw('SUM(amount) as monthly_coins'))
                ->where('type', 'earn')
                ->where('created_at', '>=', now()->startOfMonth())
                ->groupBy('client_id')
                ->orderByDesc('monthly_coins')
                ->limit($limit)
                ->get()
                ->map(function ($entry) {
                    $client = DB::table('clients')
                        ->select('name', 'avatar_url')
                        ->where('id', $entry->client_id)
                        ->first();
                    $entry->name = $client->name ?? 'Usuario';
                    $entry->avatar_url = $client->avatar_url ?? null;
                    return $entry;
                })
                ->toArray();
        });
    }

    public static function getClientRank(int $clientId): int
    {
        $xp = DB::table('clients')->where('id', $clientId)->value('xp_total') ?? 0;
        return DB::table('clients')
            ->where('status', 'active')
            ->where('xp_total', '>', $xp)
            ->count() + 1;
    }

    public static function clearCache(): void
    {
        Cache::forget('leaderboard:global');
        Cache::forget('leaderboard:streaks');
        Cache::forget('leaderboard:monthly');
    }
}
