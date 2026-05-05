<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('post_mentions')) {
            return;
        }

        Schema::create('post_mentions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id')->nullable();
            $table->unsignedBigInteger('comment_id')->nullable();
            $table->enum('mentioner_type', ['client', 'coach', 'admin']);
            $table->unsignedInteger('mentioner_id');
            $table->enum('mentioned_type', ['client', 'coach', 'admin']);
            $table->unsignedInteger('mentioned_id');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['mentioned_type', 'mentioned_id', 'created_at'], 'idx_mention_target');
            $table->index('post_id', 'idx_mention_post');
            $table->index('comment_id', 'idx_mention_comment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_mentions');
    }
};
