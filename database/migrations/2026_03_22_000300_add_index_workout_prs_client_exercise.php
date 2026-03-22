<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Composite index on workout_prs (client_id, exercise_name, is_current).
 *
 * Covers the PR-check query in WorkoutPlayer:
 *   WHERE client_id = ? AND exercise_name = ? AND is_current = 1
 *
 * Note: the table already has an auto-generated index named
 *   workout_prs_client_id_exercise_name_is_current_index
 * on the same three columns.  This migration adds a shorter, explicit alias
 * name used by application code (dropIndex by name in down()).  MySQL allows
 * duplicate column coverage — the query planner will pick the most selective
 * index regardless of name.  The guard below prevents double-creation on
 * environments where this migration has already been run.
 *
 * Additive-only migration — safe for shared vanilla PHP + Laravel DB.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workout_prs')) {
            return;
        }

        // is_current column may not exist in vanilla PHP DB — skip silently
        if (! Schema::hasColumn('workout_prs', 'is_current')) {
            return;
        }

        $existingKeys = collect(DB::select('SHOW INDEX FROM workout_prs'))
            ->pluck('Key_name')
            ->unique()
            ->values();

        if ($existingKeys->contains('idx_wpr_client_exercise_current')) {
            return;
        }

        Schema::table('workout_prs', function (Blueprint $table) {
            $table->index(
                ['client_id', 'exercise_name', 'is_current'],
                'idx_wpr_client_exercise_current'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('workout_prs')) {
            return;
        }

        $existingKeys = collect(DB::select('SHOW INDEX FROM workout_prs'))
            ->pluck('Key_name')
            ->unique()
            ->values();

        if (! $existingKeys->contains('idx_wpr_client_exercise_current')) {
            return;
        }

        Schema::table('workout_prs', function (Blueprint $table) {
            $table->dropIndex('idx_wpr_client_exercise_current');
        });
    }
};
