<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_availability', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coach_id');
            $table->tinyInteger('day_of_week'); // 0=Sunday to 6=Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('slot_duration_minutes')->default(30);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index('coach_id');
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coach_id');
            $table->unsignedBigInteger('client_id');
            $table->enum('type', ['checkin', 'review', 'video_call', 'consultation']);
            $table->dateTime('scheduled_at');
            $table->enum('status', ['confirmed', 'completed', 'cancelled', 'no_show'])->default('confirmed');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['coach_id', 'scheduled_at']);
            $table->index(['client_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('coach_availability');
    }
};
