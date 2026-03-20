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
        Schema::create('food_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('client_id');
            $table->string('image_path', 500)->nullable();
            $table->string('food_name', 200)->nullable();
            $table->unsignedSmallInteger('calories')->nullable();
            $table->decimal('protein', 5, 1)->nullable();
            $table->decimal('carbs', 5, 1)->nullable();
            $table->decimal('fat', 5, 1)->nullable();
            $table->json('ai_response')->nullable();
            $table->enum('source', ['manual', 'ai'])->default('manual');
            $table->timestamps();
            $table->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_analyses');
    }
};
