<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('moderation_actions')) {
            return;
        }

        Schema::create('moderation_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('actor_type', ['coach', 'admin']);
            $table->unsignedInteger('actor_id');
            $table->enum('action_type', [
                'pin', 'unpin', 'delete', 'restore',
                'make_official', 'dismiss_report', 'hide_for_review',
            ]);
            $table->enum('target_type', ['post', 'comment']);
            $table->unsignedBigInteger('target_id');
            $table->string('reason', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['actor_id', 'actor_type', 'created_at'], 'idx_mod_actor');
            $table->index(['target_type', 'target_id'], 'idx_mod_target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_actions');
    }
};
