<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Composite index on workout_logs (session_id, exercise_name, set_number, block_order).
 *
 * Covers the idempotency check in WorkoutPlayer::completeSet():
 *   WHERE session_id = ? AND exercise_name = ? AND set_number = ? AND block_order = ?
 *
 * Additive-only migration — safe for shared vanilla PHP + Laravel DB.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workout_logs')) {
            return;
        }

        foreach (['session_id', 'exercise_name', 'set_number', 'block_order'] as $column) {
            if (! Schema::hasColumn('workout_logs', $column)) {
                return;
            }
        }

        $existingKeys = collect(DB::select('SHOW INDEX FROM workout_logs'))
            ->pluck('Key_name')
            ->unique()
            ->values();

        if ($existingKeys->contains('idx_wlogs_session_exercise_set')) {
            return;
        }

        Schema::table('workout_logs', function (Blueprint $table) {
            $table->index(
                ['session_id', 'exercise_name', 'set_number', 'block_order'],
                'idx_wlogs_session_exercise_set'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('workout_logs')) {
            return;
        }

        $existingKeys = collect(DB::select('SHOW INDEX FROM workout_logs'))
            ->pluck('Key_name')
            ->unique()
            ->values();

        if (! $existingKeys->contains('idx_wlogs_session_exercise_set')) {
            return;
        }

        Schema::table('workout_logs', function (Blueprint $table) {
            $table->dropIndex('idx_wlogs_session_exercise_set');
        });
    }
};
