<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('coach_notification_preferences')) {
            return;
        }

        Schema::create('coach_notification_preferences', function (Blueprint $table) {
            $table->unsignedInteger('coach_id')->primary();
            $table->boolean('notify_pr_broken')->default(true);
            $table->boolean('notify_streak_milestone')->default(true);
            $table->boolean('notify_post_created')->default(false);
            $table->boolean('notify_comment_on_my_reply')->default(true);
            $table->boolean('notify_at_risk_client')->default(true);
            $table->boolean('notify_official_post_engagement')->default(true);
            $table->boolean('notify_admin_broadcast')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_notification_preferences');
    }
};
