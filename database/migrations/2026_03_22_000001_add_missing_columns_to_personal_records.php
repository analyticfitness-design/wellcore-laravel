<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add columns that may be missing from the vanilla PHP personal_records table.
     * All additions are idempotent via hasColumn guards.
     */
    public function up(): void
    {
        if (! Schema::hasTable('personal_records')) {
            return;
        }

        Schema::table('personal_records', function (Blueprint $table) {
            if (! Schema::hasColumn('personal_records', 'category')) {
                $table->string('category', 50)->default('fuerza');
            }

            if (! Schema::hasColumn('personal_records', 'duration_sec')) {
                $table->unsignedSmallInteger('duration_sec')->nullable();
            }

            if (! Schema::hasColumn('personal_records', 'distance_km')) {
                $table->decimal('distance_km', 6, 2)->nullable();
            }

            if (! Schema::hasColumn('personal_records', 'is_current')) {
                $table->boolean('is_current')->default(true);
            }
        });
    }

    public function down(): void
    {
        //
    }
};
