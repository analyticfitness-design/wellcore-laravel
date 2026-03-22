<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add columns used by the Laravel WorkoutPlayer that are missing from the
 * vanilla-PHP workout_logs schema.
 *
 * Actual vanilla columns: id, session_id, client_id, exercise_name,
 *   block_type, block_order, exercise_index, set_number, weight_kg,
 *   reps_done, rpe_actual, completed, notes, created_at
 *
 * Missing columns (added here):
 *   reps         INT          DEFAULT 0  (Laravel uses 'reps'; vanilla uses 'reps_done')
 *   target_reps  VARCHAR(20)  NULLABLE
 *   target_weight DECIMAL(6,2) NULLABLE
 *   is_pr        BOOLEAN      DEFAULT false
 *
 * Columns that already exist and do NOT need adding:
 *   block_type, block_order (already present)
 *
 * All additions are guarded with hasColumn() — safe on any environment.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workout_logs')) {
            return;
        }

        Schema::table('workout_logs', function (Blueprint $table) {
            // 'reps' — Laravel name; vanilla uses 'reps_done'. Add 'reps' so
            // Eloquent writes can use the same key without touching vanilla data.
            if (! Schema::hasColumn('workout_logs', 'reps')) {
                $table->integer('reps')->default(0)->after('weight_kg');
            }

            if (! Schema::hasColumn('workout_logs', 'target_reps')) {
                $table->string('target_reps', 20)->nullable()->after('reps');
            }

            if (! Schema::hasColumn('workout_logs', 'target_weight')) {
                $table->decimal('target_weight', 6, 2)->nullable()->after('target_reps');
            }

            if (! Schema::hasColumn('workout_logs', 'is_pr')) {
                $table->boolean('is_pr')->default(false)->after('completed');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('workout_logs')) {
            return;
        }

        Schema::table('workout_logs', function (Blueprint $table) {
            foreach (['reps', 'target_reps', 'target_weight', 'is_pr'] as $col) {
                if (Schema::hasColumn('workout_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
