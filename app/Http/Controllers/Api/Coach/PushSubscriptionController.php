<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\CoachNotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $data = $request->validate([
            'endpoint' => 'required|string|max:500',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'user_agent' => 'nullable|string|max:255',
        ]);

        $existingId = DB::table('coach_push_subscriptions')
            ->where('coach_id', $coach->id)
            ->where('endpoint', $data['endpoint'])
            ->value('id');

        if ($existingId) {
            DB::table('coach_push_subscriptions')->where('id', $existingId)->update([
                'p256dh' => $data['keys']['p256dh'],
                'auth_key' => $data['keys']['auth'],
                'user_agent' => $data['user_agent'] ?? null,
                'active' => true,
                'last_used_at' => now(),
            ]);

            return response()->json(['id' => $existingId, 'active' => true], 201);
        }

        $id = DB::table('coach_push_subscriptions')->insertGetId([
            'coach_id' => $coach->id,
            'endpoint' => $data['endpoint'],
            'p256dh' => $data['keys']['p256dh'],
            'auth_key' => $data['keys']['auth'],
            'user_agent' => $data['user_agent'] ?? null,
            'active' => true,
            'created_at' => now(),
        ]);

        return response()->json(['id' => $id, 'active' => true], 201);
    }

    public function unsubscribe(Request $request, int $id): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $affected = DB::table('coach_push_subscriptions')
            ->where('id', $id)
            ->where('coach_id', $coach->id)
            ->update(['active' => false]);

        if ($affected === 0) {
            abort(404);
        }

        return response()->json(null, 204);
    }

    public function preferences(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $prefs = CoachNotificationPreference::forCoach($coach->id);

        return response()->json($prefs);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $allowed = [
            'notify_pr_broken', 'notify_streak_milestone', 'notify_post_created',
            'notify_comment_on_my_reply', 'notify_at_risk_client',
            'notify_official_post_engagement', 'notify_admin_broadcast',
            'push_enabled', 'in_app_enabled',
        ];

        $rules = [];
        foreach ($allowed as $field) {
            $rules[$field] = 'sometimes|boolean';
        }

        $data = $request->validate($rules);

        $prefs = CoachNotificationPreference::forCoach($coach->id);
        $prefs->fill($data)->save();

        return response()->json($prefs->fresh());
    }

    private function isCoach(mixed $user): bool
    {
        if (! $user instanceof Admin) {
            return false;
        }

        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        return $role === 'coach';
    }
}
