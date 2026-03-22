<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add columns used by the Laravel WorkoutPr model that may be missing
 * from the vanilla-PHP workout_prs schema.
 *
 * Vanilla PHP likely has: id, client_id, exercise_name, weight_kg, created_at
 * Laravel model needs:
 *   reps        INT          NULLABLE
 *   volume      DECIMAL(8,2) NULLABLE  (weight_kg × reps)
 *   achieved_at DATE         NULLABLE
 *   is_current  BOOLEAN      DEFAULT false
 *
 * All additions are guarded with hasColumn() — safe on any environment.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workout_prs')) {
            return;
        }

        Schema::table('workout_prs', function (Blueprint $table) {
            if (! Schema::hasColumn('workout_prs', 'reps')) {
                $table->integer('reps')->nullable();
            }

            if (! Schema::hasColumn('workout_prs', 'volume')) {
                $table->decimal('volume', 8, 2)->nullable();
            }

            if (! Schema::hasColumn('workout_prs', 'achieved_at')) {
                $table->date('achieved_at')->nullable();
            }

            if (! Schema::hasColumn('workout_prs', 'is_current')) {
                $table->boolean('is_current')->default(false);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('workout_prs')) {
            return;
        }

        Schema::table('workout_prs', function (Blueprint $table) {
            foreach (['reps', 'volume', 'achieved_at', 'is_current'] as $col) {
                if (Schema::hasColumn('workout_prs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
