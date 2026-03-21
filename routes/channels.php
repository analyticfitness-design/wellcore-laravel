<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CoachMessage;
use Illuminate\Support\Facades\Broadcast;

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
        'id'   => $user->id,
        'name' => $user->name ?? 'User',
        'type' => $user instanceof Admin ? 'coach' : 'client',
    ];
});
