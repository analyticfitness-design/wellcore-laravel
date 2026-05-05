<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_notification_preferences')) {
            return;
        }

        Schema::create('admin_notification_preferences', function (Blueprint $table) {
            $table->unsignedInteger('admin_id')->primary();
            $table->boolean('notify_post_reported')->default(true);
            $table->boolean('notify_coach_no_activity_7d')->default(true);
            $table->boolean('notify_thread_conflict')->default(true);
            $table->boolean('notify_broadcast_sent')->default(false);
            $table->boolean('notify_client_spam')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notification_preferences');
    }
};
