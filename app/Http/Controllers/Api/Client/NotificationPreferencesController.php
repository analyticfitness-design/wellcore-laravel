<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientNotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferencesController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $client = $request->user();
        abort_unless($client instanceof Client, 403);
        $prefs = ClientNotificationPreference::forClient($client->id);

        return response()->json($prefs);
    }

    public function update(Request $request): JsonResponse
    {
        $client = $request->user();
        abort_unless($client instanceof Client, 403);

        $allowed = [
            'notify_post_reactions', 'notify_comments_on_my_post', 'notify_mentions',
            'notify_coach_messages', 'notify_coach_announcements', 'notify_wellcore_announcements',
            'push_enabled', 'in_app_enabled',
        ];

        $rules = [];
        foreach ($allowed as $field) {
            $rules[$field] = 'sometimes|boolean';
        }

        $data = $request->validate($rules);

        $prefs = ClientNotificationPreference::forClient($client->id);
        $prefs->fill($data)->save();

        return response()->json($prefs->fresh());
    }
}
