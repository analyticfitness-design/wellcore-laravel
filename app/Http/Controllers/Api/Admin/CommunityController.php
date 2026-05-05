<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AdminCommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CommunityController extends Controller
{
    public function __construct(private AdminCommunityService $service) {}

    public function pulseCrossCoach(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $period = in_array($request->query('period'), ['day', 'week', 'month'], true)
            ? (string) $request->query('period')
            : 'week';

        $payload = Cache::remember(
            key: "wc:admin-community-analytics:v1:{$period}",
            ttl: 300,
            callback: fn () => [
                'coaches' => $this->service->coachMetrics($period),
                'time_series' => $this->service->postsTimeSeries(days: 30),
                'moderation_queue_count' => $this->service->moderationQueueCount(),
                'computed_at' => now()->toIso8601String(),
            ],
        );

        return response()->json($payload);
    }

    private function isAdmin(mixed $user): bool
    {
        if (! $user instanceof Admin) {
            return false;
        }

        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        return in_array($role, ['admin', 'superadmin', 'jefe'], true);
    }
}
