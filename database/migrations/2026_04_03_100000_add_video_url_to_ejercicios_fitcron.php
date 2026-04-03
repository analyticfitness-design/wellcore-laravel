<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ejercicios_fitcron')) {
            return; // table lives in a different DB in some environments
        }
        if (Schema::hasColumn('ejercicios_fitcron', 'video_url')) {
            return;
        }
        Schema::table('ejercicios_fitcron', function (Blueprint $table) {
            $table->string('video_url', 500)->nullable()->after('sin_fondo_listo');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('ejercicios_fitcron')) {
            return;
        }
        Schema::table('ejercicios_fitcron', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
};
