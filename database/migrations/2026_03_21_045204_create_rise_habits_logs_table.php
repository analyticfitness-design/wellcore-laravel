<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rise_habits_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('rise_program_id');
            $table->unsignedInteger('client_id');
            $table->date('log_date');
            $table->decimal('water_liters', 3, 1)->nullable();
            $table->decimal('sleep_hours', 3, 1)->nullable();
            $table->unsignedInteger('steps')->nullable();
            $table->boolean('meditation')->default(false);
            $table->boolean('training_completed')->default(false);
            $table->boolean('nutrition_followed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['rise_program_id', 'log_date']);
            $table->index(['client_id', 'log_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rise_habits_logs');
    }
};
