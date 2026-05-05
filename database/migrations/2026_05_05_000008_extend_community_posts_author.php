<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('community_posts', 'author_type')) {
                $table->enum('author_type', ['client', 'coach', 'admin'])
                    ->default('client')
                    ->after('client_id');
            }
            if (!Schema::hasColumn('community_posts', 'author_admin_id')) {
                $table->unsignedInteger('author_admin_id')->nullable()->after('author_type');
            }
            if (!Schema::hasColumn('community_posts', 'is_official')) {
                $table->boolean('is_official')->default(false)->after('author_admin_id');
            }
            if (!Schema::hasColumn('community_posts', 'is_global')) {
                $table->boolean('is_global')->default(false)->after('is_official');
            }
        });

        // Add the index in a separate Schema::table closure to avoid Doctrine DBAL on enum
        if (!$this->indexExists('community_posts', 'idx_posts_official')) {
            Schema::table('community_posts', function (Blueprint $table) {
                $table->index(['is_official', 'is_global', 'created_at'], 'idx_posts_official');
            });
        }
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            if ($this->indexExists('community_posts', 'idx_posts_official')) {
                $table->dropIndex('idx_posts_official');
            }
            foreach (['is_global', 'is_official', 'author_admin_id', 'author_type'] as $col) {
                if (Schema::hasColumn('community_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = \DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$index]);
        return count($rows) > 0;
    }
};
