<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_swaps', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id');
            $table->unsignedBigInteger('recipe_id');
            $table->string('recipe_name');
            $table->string('original_meal_name');
            $table->date('swap_date');
            $table->integer('calories')->default(0);
            $table->integer('protein_g')->default(0);
            $table->integer('carbs_g')->default(0);
            $table->integer('fat_g')->default(0);
            $table->integer('calories_diff')->default(0);
            $table->integer('protein_diff')->default(0);
            $table->integer('carbs_diff')->default(0);
            $table->integer('fat_diff')->default(0);
            $table->timestamps();

            $table->index(['client_id', 'swap_date']);
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_swaps');
    }
};
