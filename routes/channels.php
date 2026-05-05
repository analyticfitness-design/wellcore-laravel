<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CoachMessage;
use App\Models\PodMember;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Channel format for conversations: "conversation.{coachId}-{clientId}"
| This mirrors the CoachMessage table which uses coach_id + client_id
| (no generic conversation_id column exists in the WellCore schema).
|
*/

// Private conversation channel — only the coach and their client can listen.
// Channel name format: conversation.{coachId}-{clientId}
Broadcast::channel('conversation.{conversationId}', function ($user, string $conversationId) {
    [$coachId, $clientId] = array_map('intval', explode('-', $conversationId, 2));

    if ($user instanceof Admin) {
        // Coach can only listen to their own conversations
        return (int) $user->id === $coachId;
    }

    if ($user instanceof Client) {
        // Client can only listen to conversations they are part of
        return (int) $user->id === $clientId && CoachMessage::where('coach_id', $coachId)
            ->where('client_id', $clientId)
            ->exists();
    }

    return false;
});

// Private per-user notification channel — each user only accesses their own channel.
// Supports both Admin and Client user types.
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Presence channel for online users — visible on the coach dashboard.
// Returns user identity data for the presence member list.
Broadcast::channel('online-users', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name ?? 'User',
        'type' => $user instanceof Admin ? 'coach' : 'client',
    ];
});

// Private RISE pod channel — only pod members can listen.
// Channel name format: rise-pod.{podId}
Broadcast::channel('rise-pod.{podId}', function ($user, int $podId) {
    if (! ($user instanceof Client)) {
        return false;
    }

    return PodMember::where('pod_id', $podId)
        ->where('client_id', $user->id)
        ->exists();
});

// === Community Cross-Role channels (Fase A) ===

/**
 * Normalize a user's role to a string. Admin::role is cast to UserRole BackedEnum,
 * so plain string comparison won't work directly. This helper accepts either form.
 */
$wcResolveRole = static function ($role): ?string {
    if ($role instanceof BackedEnum) {
        return (string) $role->value;
    }

    return $role !== null ? (string) $role : null;
};

// Coach listens to activity from THEIR clients (community-scoped activity feed).
// Channel name format: coach.{coachId}.community
Broadcast::channel('coach.{coachId}.community', function ($user, int $coachId) use ($wcResolveRole) {
    if (! ($user instanceof Admin)) {
        return false;
    }
    $role = $wcResolveRole($user->role);

    return (int) $user->id === $coachId
        && in_array($role, ['coach', 'admin', 'superadmin'], true);
});

// Admin listens to GLOBAL community activity (cross-coach moderation, broadcasts).
Broadcast::channel('admin.community', function ($user) use ($wcResolveRole) {
    if (! ($user instanceof Admin)) {
        return false;
    }
    $role = $wcResolveRole($user->role);

    return in_array($role, ['admin', 'superadmin', 'jefe'], true);
});

// Per-user mention channel (any role). Channel: user.{type}.{id}
// Disambiguates from the generic 'user.{userId}' channel above.
Broadcast::channel('user.{type}.{id}', function ($user, string $type, int $id) use ($wcResolveRole) {
    if ((int) $user->id !== $id) {
        return false;
    }

    if ($type === 'client') {
        return $user instanceof Client;
    }

    if ($type === 'coach') {
        return $user instanceof Admin
            && $wcResolveRole($user->role) === 'coach';
    }

    if ($type === 'admin') {
        return $user instanceof Admin
            && in_array($wcResolveRole($user->role), ['admin', 'superadmin', 'jefe'], true);
    }

    return false;
});

// Private community post channel — only clients who can see this post may listen.
// A client can see the post if: the post belongs to their coach's community scope,
// which means community_posts.coach_admin_id matches the client's active coach in
// client_coach (admin_id). The channel uses a hyphen separator to avoid Reverb
// treating the dot as a nested channel segment.
// Channel name format: community-post.{postId}
Broadcast::channel('community-post.{postId}', function ($user, int $postId) {
    if (! ($user instanceof Client)) {
        return false;
    }

    // Find the client's active coach.
    $coachId = DB::table('client_coach')
        ->where('client_id', $user->id)
        ->where('active', true)
        ->value('admin_id');

    if (! $coachId) {
        // No active coach: only allow if the post was authored by this client.
        return DB::table('community_posts')
            ->where('id', $postId)
            ->where('client_id', $user->id)
            ->where('visible', true)
            ->exists();
    }

    // With a coach: allow if the post is scoped to that same coach community.
    return DB::table('community_posts')
        ->where('id', $postId)
        ->where('coach_admin_id', $coachId)
        ->where('visible', true)
        ->exists();
});
