<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('broadcast_messages')) {
            return;
        }

        Schema::create('broadcast_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('sender_type', ['admin', 'coach']);
            $table->unsignedInteger('sender_id');
            $table->enum('audience_type', ['clients', 'coaches', 'all_communities', 'segmented']);
            $table->json('segment_filter')->nullable();
            $table->string('subject', 255)->nullable();
            $table->text('body');
            $table->boolean('push_enabled')->default(false);
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->timestamp('sent_at')->useCurrent();

            $table->index(['sender_type', 'sender_id'], 'idx_broadcast_sender');
            $table->index('sent_at', 'idx_broadcast_sent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_messages');
    }
};
