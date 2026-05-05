<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Events\PostMadeOfficial;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\CommunityPost;
use App\Models\ModerationAction;
use App\Services\AdminCommunityService;
use App\Services\ModerationService;
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

    public function pinAdminOverride(Request $request, int $postId): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $request->validate([
            'hours' => 'nullable|integer|min:1|max:720',
            'note' => 'nullable|string|max:255',
        ]);

        $post = CommunityPost::findOrFail($postId);

        app(ModerationService::class)->pinPost(
            $post,
            $admin,
            'admin',
            (int) $request->input('hours', 168),
            $request->input('note') ?: null,
        );

        return response()->json(['ok' => true], 200);
    }

    public function makeGlobal(Request $request, int $postId): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isSuperadmin($admin), 403);

        $post = CommunityPost::findOrFail($postId);
        $post->update([
            'is_official' => true,
            'is_global' => true,
            'author_type' => 'admin',
            'author_admin_id' => $admin->id,
        ]);

        ModerationAction::create([
            'actor_type' => 'admin',
            'actor_id' => $admin->id,
            'action_type' => 'make_official',
            'target_type' => 'post',
            'target_id' => $post->id,
            'metadata' => ['scope' => 'global'],
            'created_at' => now(),
        ]);

        event(new PostMadeOfficial($post->id, $post->coach_admin_id, $admin->id, 'admin'));

        return response()->json(['ok' => true], 200);
    }

    private function isAdmin(mixed $user): bool
    {
        if (! $user instanceof Admin) {
            return false;
        }

        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        return in_array($role, ['admin', 'superadmin', 'jefe'], true);
    }

    private function isSuperadmin(mixed $user): bool
    {
        if (! $user instanceof Admin) {
            return false;
        }

        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        return $role === 'superadmin';
    }
}
