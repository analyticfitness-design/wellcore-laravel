<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_pulsos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->enum('pulso_type', ['entrenamiento', 'pr', 'nutricion', 'recuperacion', 'logro', 'libre'])
                  ->default('libre');
            $table->string('media_url', 500)->nullable();
            $table->enum('media_type', ['photo', 'video', 'stat_card'])->default('stat_card');
            $table->string('caption', 200)->nullable();
            $table->unsignedBigInteger('workout_session_id')->nullable();
            $table->json('stats_overlay')->nullable();
            $table->timestamp('expires_at');
            $table->boolean('is_auto_generated')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index(['client_id', 'expires_at'], 'idx_pulso_client_expires');
            $table->index('expires_at', 'idx_pulso_expires');
        });

        Schema::create('client_pulso_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pulso_id');
            $table->unsignedBigInteger('viewer_id');
            $table->timestamp('viewed_at')->useCurrent();

            $table->unique(['pulso_id', 'viewer_id'], 'uq_pulso_viewer');
            $table->index('pulso_id', 'idx_pview_pulso');
        });

        Schema::create('client_pulso_reactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pulso_id');
            $table->unsignedBigInteger('client_id');
            $table->enum('reaction_type', ['fire', 'muscle', 'trophy', 'energy']);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['pulso_id', 'client_id', 'reaction_type'], 'uq_pulso_client_reaction');
            $table->index('pulso_id', 'idx_preaction_pulso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_pulso_reactions');
        Schema::dropIfExists('client_pulso_views');
        Schema::dropIfExists('client_pulsos');
    }
};
