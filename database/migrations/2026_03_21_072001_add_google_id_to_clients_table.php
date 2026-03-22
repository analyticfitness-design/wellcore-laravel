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
            if (! Schema::hasColumn('clients', 'google_id')) {
                $table->string('google_id', 255)->nullable()->after('email');
            }
            if (! collect(Schema::getIndexes('clients'))->pluck('name')->contains('clients_google_id_index')) {
                $table->index('google_id');
            }
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
