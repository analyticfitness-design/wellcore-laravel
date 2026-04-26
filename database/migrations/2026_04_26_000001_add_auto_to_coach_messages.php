<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('coach_messages') && ! Schema::hasColumn('coach_messages', 'auto')) {
            Schema::table('coach_messages', function (Blueprint $table) {
                $table->boolean('auto')->default(false)->after('direction');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('coach_messages', 'auto')) {
            Schema::table('coach_messages', function (Blueprint $table) {
                $table->dropColumn('auto');
            });
        }
    }
};
