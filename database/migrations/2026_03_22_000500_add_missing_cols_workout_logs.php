<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add columns used by Laravel WorkoutPlayer that may not exist in the
 * vanilla-PHP workout_logs table:
 *   block_type    VARCHAR(50)  DEFAULT 'normal'
 *   block_order   INT          DEFAULT 0
 *   target_reps   VARCHAR(20)  NULLABLE
 *   target_weight DECIMAL(6,2) NULLABLE
 *   is_pr         BOOLEAN      DEFAULT false
 *
 * Each column is guarded with hasColumn() so this migration is safe to run
 * on both fresh and existing databases.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workout_logs')) {
            return;
        }

        Schema::table('workout_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('workout_logs', 'block_type')) {
                $table->string('block_type', 50)->default('normal')->after('exercise_name');
            }

            if (! Schema::hasColumn('workout_logs', 'block_order')) {
                $table->integer('block_order')->default(0)->after('block_type');
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
            $cols = ['block_type', 'block_order', 'target_reps', 'target_weight', 'is_pr'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('workout_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
