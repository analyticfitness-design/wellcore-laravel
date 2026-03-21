<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('day_name', 100)->nullable();
            $table->date('session_date');
            $table->unsignedInteger('duration_sec')->default(0);
            $table->unsignedTinyInteger('feeling')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('completed')->default(false);
            $table->unsignedInteger('total_volume_kg')->default(0);
            $table->unsignedInteger('total_reps')->default(0);
            $table->unsignedInteger('total_sets')->default(0);
            $table->unsignedInteger('xp_earned')->default(0);
            $table->timestamps();

            $table->index(['client_id', 'session_date']);
        });

        Schema::create('workout_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id')->index();
            $table->string('exercise_name', 150);
            $table->string('block_type', 30)->nullable();
            $table->unsignedTinyInteger('block_order')->default(0);
            $table->unsignedTinyInteger('set_number');
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->unsignedSmallInteger('reps')->nullable();
            $table->unsignedSmallInteger('target_reps')->nullable();
            $table->decimal('target_weight', 6, 2)->nullable();
            $table->boolean('completed')->default(false);
            $table->boolean('is_pr')->default(false);
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('workout_sessions')->onDelete('cascade');
            $table->index(['session_id', 'exercise_name']);
        });

        Schema::create('workout_prs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->string('exercise_name', 150);
            $table->decimal('weight_kg', 6, 2);
            $table->unsignedSmallInteger('reps')->default(1);
            $table->decimal('volume', 8, 2)->nullable();
            $table->date('achieved_at');
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->index(['client_id', 'exercise_name', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_logs');
        Schema::dropIfExists('workout_prs');
        Schema::dropIfExists('workout_sessions');
    }
};
