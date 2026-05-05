<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminNotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferencesController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $prefs = AdminNotificationPreference::forAdmin($admin->id);

        return response()->json($prefs);
    }

    public function update(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $allowed = [
            'notify_post_reported', 'notify_coach_no_activity_7d', 'notify_thread_conflict',
            'notify_broadcast_sent', 'notify_client_spam', 'push_enabled', 'in_app_enabled',
        ];

        $rules = [];
        foreach ($allowed as $field) {
            $rules[$field] = 'sometimes|boolean';
        }

        $data = $request->validate($rules);

        $prefs = AdminNotificationPreference::forAdmin($admin->id);
        $prefs->fill($data)->save();

        return response()->json($prefs->fresh());
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
