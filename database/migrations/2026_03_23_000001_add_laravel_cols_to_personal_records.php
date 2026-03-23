<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * The vanilla PHP personal_records table has a different schema:
 *   exercise_id (FK), value, reps, rpe, recorded_at
 *
 * The Laravel model/component expects:
 *   exercise (string), weight, achieved_at, notes, is_current, category
 *
 * This migration adds the missing Laravel columns (all nullable, idempotent)
 * and back-fills achieved_at from recorded_at and weight from value.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personal_records')) {
            return;
        }

        Schema::table('personal_records', function (Blueprint $table) {
            if (! Schema::hasColumn('personal_records', 'exercise')) {
                $table->string('exercise', 100)->nullable();
            }
            if (! Schema::hasColumn('personal_records', 'weight')) {
                $table->decimal('weight', 6, 2)->nullable();
            }
            if (! Schema::hasColumn('personal_records', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('personal_records', 'achieved_at')) {
                $table->date('achieved_at')->nullable();
            }
        });

        // Back-fill achieved_at from recorded_at (vanilla PHP column)
        if (Schema::hasColumn('personal_records', 'recorded_at')) {
            DB::statement('UPDATE personal_records SET achieved_at = DATE(recorded_at) WHERE achieved_at IS NULL AND recorded_at IS NOT NULL');
        }

        // Back-fill weight from value (vanilla PHP stores weight/distance/time as generic "value")
        if (Schema::hasColumn('personal_records', 'value')) {
            DB::statement('UPDATE personal_records SET weight = value WHERE weight IS NULL AND value IS NOT NULL AND value > 0');
        }
    }

    public function down(): void
    {
        // Intentional no-op: removing columns would destroy data.
    }
};
