<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wellcoins_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->enum('type', ['earn', 'spend']);
            $table->string('action');
            $table->integer('amount');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index(['client_id', 'type']);
            $table->index('created_at');
        });

        Schema::create('client_achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('achievement_id');
            $table->timestamp('unlocked_at');
            $table->timestamps();
            $table->unique(['client_id', 'achievement_id']);
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_achievements');
        Schema::dropIfExists('wellcoins_transactions');
    }
};
