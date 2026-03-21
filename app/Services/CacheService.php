<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public static function remember(string $key, int $minutes, callable $callback): mixed
    {
        return Cache::remember($key, now()->addMinutes($minutes), $callback);
    }

    public static function rememberForever(string $key, callable $callback): mixed
    {
        return Cache::rememberForever($key, $callback);
    }

    public static function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    public static function flush(string $prefix): void
    {
        // Flush all keys with a given prefix (useful for invalidation)
        Cache::flush();
    }

    // Common cache keys
    public const DASHBOARD_STATS = 'dashboard:stats:';
    public const COACH_CLIENTS = 'coach:clients:';
    public const BLOG_ARTICLES = 'blog:articles';
    public const PLAN_TEMPLATES = 'plan:templates';
}
