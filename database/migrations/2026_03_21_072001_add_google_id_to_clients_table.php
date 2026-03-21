<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ADDITIVE ONLY — adds google_id for OAuth login support.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('google_id', 255)->nullable()->after('email');
            $table->index('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['google_id']);
            $table->dropColumn('google_id');
        });
    }
};
