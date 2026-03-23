<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates metrics table for the Metric model (body measurements tracker).
 * Idempotent — safe to run on databases that already have this table.
 * The vanilla PHP app may already have a metrics table; this only creates it
 * if missing, ensuring both apps can coexist.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('metrics')) {
            // Table already exists (created by vanilla PHP or prior migration).
            // Ensure the Laravel-specific columns exist.
            Schema::table('metrics', function (Blueprint $table) {
                if (! Schema::hasColumn('metrics', 'porcentaje_musculo')) {
                    $table->decimal('porcentaje_musculo', 5, 2)->nullable();
                }
                if (! Schema::hasColumn('metrics', 'porcentaje_grasa')) {
                    $table->decimal('porcentaje_grasa', 5, 2)->nullable();
                }
                if (! Schema::hasColumn('metrics', 'notas')) {
                    $table->text('notas')->nullable();
                }
                if (! Schema::hasColumn('metrics', 'log_date')) {
                    $table->date('log_date')->nullable();
                }
                if (! Schema::hasColumn('metrics', 'peso')) {
                    $table->decimal('peso', 5, 2)->nullable();
                }
            });

            return;
        }

        Schema::create('metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->date('log_date')->index();
            $table->decimal('peso', 5, 2)->nullable();
            $table->decimal('porcentaje_musculo', 5, 2)->nullable();
            $table->decimal('porcentaje_grasa', 5, 2)->nullable();
            $table->text('notas')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        // Intentional no-op: dropping table would destroy data.
    }
};
