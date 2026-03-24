<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rise_habits_logs') && ! Schema::hasColumn('rise_habits_logs', 'habits_json')) {
            Schema::table('rise_habits_logs', function (Blueprint $table) {
                $table->json('habits_json')->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rise_habits_logs') && Schema::hasColumn('rise_habits_logs', 'habits_json')) {
            Schema::table('rise_habits_logs', function (Blueprint $table) {
                $table->dropColumn('habits_json');
            });
        }
    }
};
