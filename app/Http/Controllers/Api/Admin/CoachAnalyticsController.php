<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AdminCommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CoachAnalyticsController extends Controller
{
    public function __construct(private AdminCommunityService $service) {}

    public function show(Request $request, int $coachId): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $payload = Cache::remember(
            key: "wc:admin-coach-analytics:v1:{$coachId}",
            ttl: 600,
            callback: fn () => $this->service->coachAnalytics($coachId),
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
