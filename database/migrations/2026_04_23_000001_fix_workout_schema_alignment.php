<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aligns local dev workout schema with production vanilla PHP schema.
 *
 * Production (vanilla PHP) has:
 *   workout_sessions: duration_sec, total_volume_kg, total_reps, total_sets, xp_earned
 *   workout_logs: client_id (production only), no UNIQUE on set composite key
 *
 * This migration:
 *   1. Adds duration_sec to workout_sessions if only duration_minutes exists
 *   2. Adds total_volume_kg if only total_volume exists
 *   3. Adds total_reps, total_sets, xp_earned to workout_sessions
 *   4. Adds UNIQUE index on workout_logs(session_id, exercise_name, set_number, block_order)
 *      so that Laravel's upsert ON DUPLICATE KEY UPDATE works correctly
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── workout_sessions alignment ─────────────────────────────────────────
        Schema::table('workout_sessions', function (Blueprint $table) {
            // duration_sec is the canonical column (vanilla PHP); add it if missing
            if (! Schema::hasColumn('workout_sessions', 'duration_sec')) {
                $table->unsignedInteger('duration_sec')->default(0)->after('session_date');
            }

            // total_volume_kg is canonical; add if missing
            if (! Schema::hasColumn('workout_sessions', 'total_volume_kg')) {
                $table->decimal('total_volume_kg', 10, 2)->default(0)->after('completed');
            }

            if (! Schema::hasColumn('workout_sessions', 'total_reps')) {
                $table->unsignedInteger('total_reps')->default(0)->after('total_volume_kg');
            }

            if (! Schema::hasColumn('workout_sessions', 'total_sets')) {
                $table->unsignedInteger('total_sets')->default(0)->after('total_reps');
            }

            if (! Schema::hasColumn('workout_sessions', 'xp_earned')) {
                $table->unsignedInteger('xp_earned')->default(0)->after('total_sets');
            }
        });

        // ── workout_logs: UNIQUE index for upsert correctness ─────────────────
        // Drop the non-unique composite index first to replace with UNIQUE.
        Schema::table('workout_logs', function (Blueprint $table) {
            // Drop old non-unique composite index if it exists
            try {
                $table->dropIndex('idx_wlogs_session_exercise_set');
            } catch (\Throwable $e) {
                // Index may not exist in all environments
            }

            if (! $this->uniqueIndexExists('workout_logs', 'uq_workout_log_set')) {
                $table->unique(
                    ['session_id', 'exercise_name', 'set_number', 'block_order'],
                    'uq_workout_log_set'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('workout_sessions', function (Blueprint $table) {
            foreach (['total_reps', 'total_sets', 'xp_earned'] as $col) {
                if (Schema::hasColumn('workout_sessions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('workout_logs', function (Blueprint $table) {
            try {
                $table->dropUnique('uq_workout_log_set');
                $table->index(
                    ['session_id', 'exercise_name', 'set_number', 'block_order'],
                    'idx_wlogs_session_exercise_set'
                );
            } catch (\Throwable $e) {
                //
            }
        });
    }

    private function uniqueIndexExists(string $table, string $indexName): bool
    {
        $indexes = collect(\DB::select("SHOW INDEX FROM `{$table}`"));
        return $indexes->contains(fn ($i) => $i->Key_name === $indexName && $i->Non_unique === 0);
    }
};
