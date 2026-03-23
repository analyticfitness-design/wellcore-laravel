<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * The first Laravel migration (000001) added: exercise, weight, notes, achieved_at.
 * The Laravel component also writes: category, duration_sec, distance_km, is_current.
 * This migration adds those remaining columns (idempotent, all nullable).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personal_records')) {
            return;
        }

        Schema::table('personal_records', function (Blueprint $table) {
            if (! Schema::hasColumn('personal_records', 'category')) {
                $table->string('category', 50)->nullable()->default('fuerza');
            }
            if (! Schema::hasColumn('personal_records', 'duration_sec')) {
                $table->unsignedInteger('duration_sec')->nullable();
            }
            if (! Schema::hasColumn('personal_records', 'distance_km')) {
                $table->decimal('distance_km', 8, 3)->nullable();
            }
            if (! Schema::hasColumn('personal_records', 'is_current')) {
                $table->boolean('is_current')->nullable()->default(false);
            }
        });
    }

    public function down(): void
    {
        // Intentional no-op: removing columns would destroy data.
    }
};
