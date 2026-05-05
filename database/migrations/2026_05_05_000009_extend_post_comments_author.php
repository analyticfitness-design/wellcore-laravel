<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('post_comments', 'author_type')) {
                $table->enum('author_type', ['client', 'coach', 'admin'])
                    ->default('client')
                    ->after('client_id');
            }
            if (! Schema::hasColumn('post_comments', 'author_admin_id')) {
                $table->unsignedInteger('author_admin_id')->nullable()->after('author_type');
            }
        });

        if (! $this->indexExists('post_comments', 'idx_comments_author')) {
            Schema::table('post_comments', function (Blueprint $table) {
                $table->index(['author_type', 'author_admin_id'], 'idx_comments_author');
            });
        }
    }

    public function down(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            if ($this->indexExists('post_comments', 'idx_comments_author')) {
                $table->dropIndex('idx_comments_author');
            }
            foreach (['author_admin_id', 'author_type'] as $col) {
                if (Schema::hasColumn('post_comments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$index]);

        return count($rows) > 0;
    }
};
