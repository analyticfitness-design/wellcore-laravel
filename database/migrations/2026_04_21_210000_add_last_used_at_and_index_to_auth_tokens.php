<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tokenIndexExists = DB::select(
            "SHOW INDEXES FROM auth_tokens WHERE Key_name = 'idx_auth_tokens_token'"
        );

        Schema::table('auth_tokens', function (Blueprint $table) use ($tokenIndexExists) {
            if (! Schema::hasColumn('auth_tokens', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->after('expires_at');
            }

            if (empty($tokenIndexExists)) {
                $table->index('token', 'idx_auth_tokens_token');
            }

            $existingUserExpiry = DB::select(
                "SHOW INDEXES FROM auth_tokens WHERE Key_name = 'idx_auth_tokens_user_expiry'"
            );
            if (empty($existingUserExpiry)) {
                $table->index(['user_id', 'expires_at'], 'idx_auth_tokens_user_expiry');
            }
        });
    }

    public function down(): void
    {
        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->dropColumn('last_used_at');
            $table->dropIndex('idx_auth_tokens_user_expiry');
        });
    }
};
