<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2.4 — Additive migration: track when an admin last rotated their password.
 * Safe on shared prod DB: only adds a nullable timestamp column.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admins')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            if (! Schema::hasColumn('admins', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('must_change_password');
            }
        });
    }

    public function down(): void
    {
        // No-op: additive only, never drop.
    }
};
