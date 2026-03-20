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
        Schema::create('personal_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('client_id');
            $table->string('exercise', 100);
            $table->string('category', 50)->default('fuerza');
            $table->decimal('weight', 6, 2)->nullable();
            $table->unsignedSmallInteger('reps')->nullable();
            $table->unsignedSmallInteger('duration_sec')->nullable();
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->date('achieved_at');
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->index(['client_id', 'exercise']);
            $table->index(['client_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_records');
    }
};
