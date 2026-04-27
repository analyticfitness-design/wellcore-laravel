<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function unreadCount(): JsonResponse
    {
        $count = DB::table('community_notifications')
            ->where('recipient_id', auth('wellcore')->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function index(): JsonResponse
    {
        $notifications = DB::table('community_notifications as cn')
            ->leftJoin('clients as actor', 'cn.actor_id', '=', 'actor.id')
            ->where('cn.recipient_id', auth('wellcore')->id())
            ->select('cn.*', 'actor.name as actor_name')
            ->orderByDesc('cn.created_at')
            ->limit(50)
            ->get();

        return response()->json(['notifications' => $notifications]);
    }

    public function markRead(int $id): JsonResponse
    {
        DB::table('community_notifications')
            ->where('id', $id)
            ->where('recipient_id', auth('wellcore')->id())
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function markAllRead(): JsonResponse
    {
        DB::table('community_notifications')
            ->where('recipient_id', auth('wellcore')->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
