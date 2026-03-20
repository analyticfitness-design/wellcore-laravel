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
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('client_id');
            $table->text('content');
            $table->enum('post_type', ['text', 'achievement', 'pr', 'photo'])->default('text');
            $table->string('image_path', 500)->nullable();
            $table->boolean('visible')->default(true);
            $table->timestamps();
            $table->index('client_id');
        });

        Schema::create('post_reactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedInteger('client_id');
            $table->enum('reaction_type', ['like', 'fire', 'muscle', 'clap'])->default('like');
            $table->timestamp('created_at')->nullable();
            $table->unique(['post_id', 'client_id', 'reaction_type']);
            $table->index('post_id');
        });

        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedInteger('client_id');
            $table->text('content');
            $table->timestamp('created_at')->nullable();
            $table->index('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_comments');
        Schema::dropIfExists('post_reactions');
        Schema::dropIfExists('community_posts');
    }
};
