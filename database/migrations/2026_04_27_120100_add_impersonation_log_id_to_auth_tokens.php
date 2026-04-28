<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('impersonation_log_id')
                ->nullable()
                ->after('expires_at')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->dropIndex(['impersonation_log_id']);
            $table->dropColumn('impersonation_log_id');
        });
    }
};
