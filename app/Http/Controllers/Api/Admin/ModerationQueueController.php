<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PostReport;
use App\Services\ModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModerationQueueController extends Controller
{
    public function __construct(private ModerationService $moderation) {}

    public function index(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $page = PostReport::query()
            ->with(['post', 'reporter'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($page);
    }

    public function dismiss(Request $request, int $reportId): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $report = PostReport::findOrFail($reportId);
        $this->moderation->dismissReport($report, $admin);

        return response()->json(['ok' => true]);
    }

    public function action(Request $request, int $reportId): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $data = $request->validate([
            'action' => 'required|in:hide,delete',
            'reason' => 'nullable|string',
        ]);
        $report = PostReport::findOrFail($reportId);

        $this->moderation->deletePost(
            $report->post,
            $admin,
            'admin',
            $data['reason'] ?? "queue:{$data['action']}",
        );

        $report->update([
            'status' => 'actioned',
            'reviewed_by_admin_id' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return response()->json(['ok' => true]);
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
