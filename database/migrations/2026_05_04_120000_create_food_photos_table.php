<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('food_photos')) {
            return;
        }

        Schema::create('food_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('client_id');
            $table->string('meal_name', 255);
            $table->unsignedTinyInteger('meal_index')->default(0);
            $table->date('photo_date');
            $table->string('filename', 255);
            $table->unsignedInteger('file_size')->nullable();
            $table->boolean('coach_seen')->default(false);
            $table->timestamp('coach_seen_at')->nullable();
            $table->enum('coach_reaction', ['bien', 'mejorar'])->nullable();
            $table->text('coach_note')->nullable();
            $table->boolean('xp_awarded')->default(false);
            $table->json('ai_analysis')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'meal_index', 'photo_date'], 'uq_food_photos_client_meal_date');
            $table->index(['client_id', 'photo_date'], 'idx_food_photos_client_date');
            $table->index(['coach_seen', 'created_at'], 'idx_food_photos_coach_pending');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_photos');
    }
};
