<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Policies\CommunityPostPolicy;
use App\Services\ModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function __construct(
        private ModerationService $moderation,
        private CommunityPostPolicy $policy,
    ) {}

    public function pin(Request $request, int $postId): JsonResponse
    {
        $post = CommunityPost::findOrFail($postId);
        $coach = $request->user();
        abort_unless($coach && $this->policy->canPin($coach, $post), 403);

        $data = $request->validate([
            'hours' => 'nullable|integer|min:1|max:168',
            'note' => 'nullable|string|max:500',
        ]);

        $hours = (int) ($data['hours'] ?? 24);
        $note = $data['note'] ?? null;

        $pin = $this->moderation->pinPost($post, $coach, 'coach', $hours, $note);

        return response()->json([
            'pinned_until' => $pin->pinned_until?->toIso8601String(),
            'note' => $pin->note,
        ]);
    }

    public function unpin(Request $request, int $postId): JsonResponse
    {
        $post = CommunityPost::findOrFail($postId);
        $coach = $request->user();
        abort_unless($coach && $this->policy->canPin($coach, $post), 403);

        $this->moderation->unpinPost($post, $coach, 'coach');

        return response()->json(['ok' => true]);
    }

    public function delete(Request $request, int $postId): JsonResponse
    {
        $post = CommunityPost::findOrFail($postId);
        $coach = $request->user();
        abort_unless($coach && $this->policy->canDelete($coach, $post), 403);

        $reason = $request->input('reason');
        $this->moderation->deletePost($post, $coach, 'coach', $reason);

        return response()->json(['ok' => true]);
    }

    public function makeOfficial(Request $request, int $postId): JsonResponse
    {
        $post = CommunityPost::findOrFail($postId);
        $coach = $request->user();
        abort_unless($coach && $this->policy->canMakeOfficial($coach, $post), 403);

        $this->moderation->makeOfficial($post, $coach, 'coach');

        return response()->json(['ok' => true]);
    }
}
