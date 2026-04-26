<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add isometric-exercise support fields to workout_logs.
     *
     * duration_seconds — nullable, preserves existing rows untouched.
     * is_isometric     — boolean DEFAULT false (0), safe for vanilla PHP reads.
     *
     * Additive only: no destructive changes. Compatible with the shared
     * wellcore_fitness database (Strangler Fig — vanilla PHP + Laravel).
     */
    public function up(): void
    {
        Schema::table('workout_logs', function (Blueprint $t) {
            $t->unsignedInteger('duration_seconds')->nullable()->after('duration_minutes');
            $t->boolean('is_isometric')->default(false)->after('is_cardio');
        });
    }

    public function down(): void
    {
        Schema::table('workout_logs', function (Blueprint $t) {
            $t->dropColumn(['duration_seconds', 'is_isometric']);
        });
    }
};
