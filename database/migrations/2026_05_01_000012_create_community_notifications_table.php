<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `community_notifications` table for SP-4 Community feed.
 *
 * Stores in-app notifications scoped to community events: reactions,
 * comments, follows, and medal achievements.
 *
 * Kept separate from the existing `notifications` table (system/admin
 * notifications) to avoid schema coupling with the vanilla PHP app.
 *
 * recipient_id / actor_id reference clients.id (increments = unsigned INT).
 * post_id references community_posts.id (bigIncrements = unsigned BIGINT).
 *
 * Index on (recipient_id, read_at) supports the common query:
 *   WHERE recipient_id = ? AND read_at IS NULL
 *
 * SAFE: guarded by hasTable() — never runs twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('community_notifications')) {
            Schema::create('community_notifications', function (Blueprint $t) {
                $t->id();
                $t->unsignedInteger('recipient_id');
                $t->unsignedInteger('actor_id')->nullable();
                $t->string('type', 32);          // 'reaction' | 'comment' | 'follow' | 'medal'
                $t->unsignedBigInteger('post_id')->nullable();
                $t->json('data')->nullable();
                $t->timestamp('read_at')->nullable();
                $t->timestamps();

                $t->index(['recipient_id', 'read_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('community_notifications');
    }
};
