<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates biometric_logs table for the BiometricLog model.
 * Used by MetricsTracker to sync weight entries from the metrics form.
 * Idempotent — safe to run on databases that already have this table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('biometric_logs')) {
            return;
        }

        Schema::create('biometric_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->date('log_date')->index();
            $table->unsignedInteger('steps')->nullable();
            $table->decimal('sleep_hours', 4, 1)->nullable();
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedSmallInteger('calories')->nullable();
            $table->string('source', 50)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('body_fat_pct', 4, 1)->nullable();
            $table->decimal('waist_cm', 5, 1)->nullable();
            $table->decimal('hip_cm', 5, 1)->nullable();
            $table->tinyInteger('energy_level')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'log_date']);
        });
    }

    public function down(): void
    {
        // Intentional no-op: dropping table would destroy data.
    }
};
