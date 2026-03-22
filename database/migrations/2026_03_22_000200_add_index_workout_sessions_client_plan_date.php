<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Composite index on workout_sessions (client_id, plan_id, session_date, completed).
 *
 * Covers the WorkoutPlayer resume-session query:
 *   WHERE client_id = ? AND plan_id = ? AND session_date = ? AND completed = 0
 *
 * Note: the table already has workout_sessions_client_id_session_date_index
 * (client_id, session_date).  This covering index adds plan_id and completed
 * so the resume query can be satisfied entirely from the index without a
 * table row-lookup.
 *
 * Additive-only migration — safe for shared vanilla PHP + Laravel DB.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workout_sessions')) {
            return;
        }

        $existingKeys = collect(DB::select('SHOW INDEX FROM workout_sessions'))
            ->pluck('Key_name')
            ->unique()
            ->values();

        if ($existingKeys->contains('idx_ws_client_plan_date')) {
            return;
        }

        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->index(
                ['client_id', 'plan_id', 'session_date', 'completed'],
                'idx_ws_client_plan_date'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('workout_sessions')) {
            return;
        }

        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_ws_client_plan_date');
        });
    }
};
