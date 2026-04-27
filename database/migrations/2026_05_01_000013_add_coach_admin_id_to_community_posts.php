<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `coach_admin_id` to `community_posts` for SP-4 Coach-Scoped Community.
 *
 * Enables posts to be associated with the coach (admin) who authored or
 * pinned them, supporting scoped feeds where clients see posts from their
 * assigned coach's community space.
 *
 * admins.id is an increments() (unsigned INT 32-bit) column — use unsignedInteger.
 * Nullable because existing posts were created before coach-scoping existed.
 *
 * Index is added for the common feed query:
 *   WHERE coach_admin_id = ? ORDER BY created_at DESC
 *
 * SAFE: guarded by hasColumn() — never runs twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('community_posts', 'coach_admin_id')) {
            Schema::table('community_posts', function (Blueprint $t) {
                $t->unsignedInteger('coach_admin_id')->nullable()->after('client_id')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $t) {
            if (Schema::hasColumn('community_posts', 'coach_admin_id')) {
                $t->dropIndex(['coach_admin_id']);
                $t->dropColumn('coach_admin_id');
            }
        });
    }
};
