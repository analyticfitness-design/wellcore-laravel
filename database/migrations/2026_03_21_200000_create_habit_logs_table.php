<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create habit_logs only when it doesn't exist.
     * The vanilla PHP app may have created this table already;
     * this migration is a safe idempotent guard.
     */
    public function up(): void
    {
        if (Schema::hasTable('habit_logs')) {
            return;
        }

        Schema::create('habit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('client_id')->index();
            $table->date('log_date');
            $table->string('habit_type', 50); // agua, sueno, nutricion, estres, entrenamiento, suplementos
            $table->integer('value')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index(['client_id', 'log_date']);
            $table->index(['client_id', 'habit_type', 'log_date'], 'idx_habit_client_type_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habit_logs');
    }
};
