<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_notification_preferences')) {
            return;
        }

        Schema::create('client_notification_preferences', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->primary();
            $table->boolean('notify_post_reactions')->default(true);
            $table->boolean('notify_comments_on_my_post')->default(true);
            $table->boolean('notify_mentions')->default(true);
            $table->boolean('notify_coach_messages')->default(true);
            $table->boolean('notify_coach_announcements')->default(true);
            $table->boolean('notify_wellcore_announcements')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_notification_preferences');
    }
};
