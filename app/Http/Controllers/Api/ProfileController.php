<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function show(int $clientId): JsonResponse
    {
        $viewer = auth('wellcore')->user();

        $client = Client::with('medals')->findOrFail($clientId);

        if (! $this->viewerCanSeeProfile($viewer, $clientId)) {
            abort(403, 'Este perfil pertenece a otra comunidad.');
        }

        $xp = $this->loadXp($clientId);

        $isFollowing = $viewer->following()->where('follows.followed_id', $clientId)->exists();

        return response()->json([
            'id' => $client->id,
            'name' => $client->name,
            'avatar' => $client->avatar_url ?? null,
            'bio' => $client->bio ?? null,
            'city' => $client->city ?? null,
            'started_at' => $client->created_at?->format('M Y'),
            'streak_days' => $xp?->streak_days ?? 0,
            'level' => $xp?->level ?? 1,
            'xp_total' => $xp?->xp_total ?? 0,
            'medals' => $client->medals->map(fn ($m) => [
                'name' => $m->name,
                'icon' => $m->icon,
                'achieved_at' => $m->pivot->achieved_at,
            ]),
            'is_following' => $isFollowing,
            'follower_count' => $client->followers()->count(),
            'following_count' => $client->following()->count(),
        ]);
    }

    public function follow(int $clientId): JsonResponse
    {
        $viewer = auth('wellcore')->user();

        if ($viewer->id === $clientId) {
            return response()->json(['error' => 'No puedes seguirte a ti mismo.'], 400);
        }

        $viewer->following()->syncWithoutDetaching([$clientId]);

        DB::table('community_notifications')->insert([
            'recipient_id' => $clientId,
            'actor_id' => $viewer->id,
            'type' => 'follow',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function unfollow(int $clientId): JsonResponse
    {
        auth('wellcore')->user()->following()->detach($clientId);

        return response()->json(['ok' => true]);
    }

    private function viewerCanSeeProfile(Client $viewer, int $clientId): bool
    {
        if ($viewer->id === $clientId) {
            return true;
        }

        $isFollowing = $viewer->following()->where('follows.followed_id', $clientId)->exists();
        if ($isFollowing) {
            return true;
        }

        // Merge new client_coach table with legacy clients.coach_id
        $viewerCoachIds = DB::table('client_coach')->where('client_id', $viewer->id)->where('active', true)->pluck('admin_id')
            ->push(DB::table('clients')->where('id', $viewer->id)->value('coach_id'))->filter()->unique();

        $clientCoachIds = DB::table('client_coach')->where('client_id', $clientId)->where('active', true)->pluck('admin_id')
            ->push(DB::table('clients')->where('id', $clientId)->value('coach_id'))->filter()->unique();

        return $viewerCoachIds->intersect($clientCoachIds)->isNotEmpty();
    }

    private function loadXp(int $clientId): ?object
    {
        try {
            return DB::table('client_xp')->where('client_id', $clientId)->first();
        } catch (\Throwable) {
            return null;
        }
    }
}
