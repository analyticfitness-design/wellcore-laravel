<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('post_reports')) {
            return;
        }

        Schema::create('post_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedInteger('reporter_id');
            $table->enum('reason', ['spam', 'offensive', 'off_topic', 'other']);
            $table->string('reason_detail', 500)->nullable();
            $table->enum('status', ['pending', 'dismissed', 'actioned'])->default('pending');
            $table->unsignedInteger('reviewed_by_admin_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['status', 'created_at'], 'idx_reports_pending');
            $table->index('post_id', 'idx_reports_post');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_reports');
    }
};
