<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The community_posts table comes from the vanilla PHP app and lacks
     * the 'visible' column that CommunityFeed queries on.
     * This migration adds it safely without touching existing rows.
     */
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('community_posts', 'visible')) {
                $table->boolean('visible')->default(true)->after('content');
            }

            if (! Schema::hasColumn('community_posts', 'image_path')) {
                $table->string('image_path', 500)->nullable()->after('visible');
            }

            if (! Schema::hasColumn('community_posts', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            foreach (['visible', 'image_path', 'updated_at'] as $col) {
                if (Schema::hasColumn('community_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
