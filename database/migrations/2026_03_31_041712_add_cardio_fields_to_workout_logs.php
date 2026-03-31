<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workout_logs', function (Blueprint $table) {
            $table->boolean('is_cardio')->default(false)->after('is_pr');
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('is_cardio');
            $table->decimal('speed_kmh', 5, 2)->nullable()->after('duration_minutes');
            $table->unsignedTinyInteger('incline_percent')->nullable()->after('speed_kmh');
            $table->unsignedSmallInteger('heart_rate_avg')->nullable()->after('incline_percent');
        });
    }

    public function down(): void
    {
        Schema::table('workout_logs', function (Blueprint $table) {
            $table->dropColumn(['is_cardio', 'duration_minutes', 'speed_kmh', 'incline_percent', 'heart_rate_avg']);
        });
    }
};
