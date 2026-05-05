<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\PostReported;
use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\PostReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostReportController extends Controller
{
    public function store(Request $request, int $postId): JsonResponse
    {
        $reporter = $request->user();
        abort_unless($reporter, 401);

        $post = CommunityPost::findOrFail($postId);

        $data = $request->validate([
            'reason' => 'required|in:spam,offensive,off_topic,other',
            'reason_detail' => 'nullable|string|max:500',
        ]);

        $exists = PostReport::where('post_id', $postId)
            ->where('reporter_id', $reporter->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'already_reported'], 409);
        }

        $report = PostReport::create([
            'post_id' => $postId,
            'reporter_id' => $reporter->id,
            'reason' => $data['reason'],
            'reason_detail' => $data['reason_detail'] ?? null,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        event(new PostReported($postId, $post->coach_admin_id, $reporter->id, $data['reason']));

        return response()->json(['report_id' => $report->id]);
    }
}
