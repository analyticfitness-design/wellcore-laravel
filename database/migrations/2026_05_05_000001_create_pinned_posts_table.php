<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pinned_posts')) {
            return;
        }

        Schema::create('pinned_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->enum('pinned_by_type', ['coach', 'admin']);
            $table->unsignedInteger('pinned_by_id');
            $table->timestamp('pinned_at');
            $table->timestamp('pinned_until')->nullable();
            $table->string('note', 255)->nullable();

            $table->index(['post_id', 'pinned_until'], 'idx_pinned_active');
            $table->index(['pinned_by_id', 'pinned_by_type'], 'idx_pinned_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinned_posts');
    }
};
