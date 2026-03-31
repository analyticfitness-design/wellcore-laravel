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
        Schema::table('biometric_logs', function (Blueprint $table) {
            $table->decimal('chest_cm', 5, 1)->nullable()->after('hip_cm');
            $table->decimal('thigh_cm', 5, 1)->nullable()->after('chest_cm');
            $table->decimal('arm_cm', 5, 1)->nullable()->after('thigh_cm');
            $table->decimal('muscle_pct', 5, 1)->nullable()->after('arm_cm');
        });
    }

    public function down(): void
    {
        Schema::table('biometric_logs', function (Blueprint $table) {
            $table->dropColumn(['chest_cm', 'thigh_cm', 'arm_cm', 'muscle_pct']);
        });
    }
};
