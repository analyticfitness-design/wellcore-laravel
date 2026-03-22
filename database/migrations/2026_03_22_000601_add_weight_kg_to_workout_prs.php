<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add weight_kg column to workout_prs table.
 *
 * The vanilla PHP schema does not have weight_kg; this adds it safely.
 * Also adds updated_at so Eloquent timestamps() works without UPDATED_AT=null.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workout_prs')) {
            return;
        }

        Schema::table('workout_prs', function (Blueprint $table) {
            if (! Schema::hasColumn('workout_prs', 'weight_kg')) {
                $table->decimal('weight_kg', 6, 2)->nullable();
            }

            if (! Schema::hasColumn('workout_prs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('workout_prs')) {
            return;
        }

        Schema::table('workout_prs', function (Blueprint $table) {
            foreach (['weight_kg', 'updated_at'] as $col) {
                if (Schema::hasColumn('workout_prs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
