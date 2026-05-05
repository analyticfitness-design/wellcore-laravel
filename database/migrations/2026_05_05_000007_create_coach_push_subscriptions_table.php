<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('coach_push_subscriptions')) {
            return;
        }

        Schema::create('coach_push_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('coach_id');
            $table->string('endpoint', 500);
            $table->text('p256dh');
            $table->text('auth_key');
            $table->string('user_agent', 255)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // MySQL key-length cap: take first 191 chars of endpoint
            $table->unique(['coach_id', 'endpoint'], 'uq_coach_endpoint');
            $table->index(['coach_id', 'active'], 'idx_coach_subs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_push_subscriptions');
    }
};
