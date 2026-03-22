<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Composite index on assigned_plans (client_id, plan_type, active).
 *
 * Covers the WorkoutPlayer mount() query:
 *   WHERE client_id = ? AND plan_type = ? AND active = 1
 *
 * Additive-only migration — safe for shared vanilla PHP + Laravel DB.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assigned_plans')) {
            return;
        }

        // Guard: skip if the index was already created by a prior run or manually.
        $existingKeys = collect(DB::select('SHOW INDEX FROM assigned_plans'))
            ->pluck('Key_name')
            ->unique()
            ->values();

        if ($existingKeys->contains('idx_ap_client_type_active')) {
            return;
        }

        Schema::table('assigned_plans', function (Blueprint $table) {
            $table->index(['client_id', 'plan_type', 'active'], 'idx_ap_client_type_active');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('assigned_plans')) {
            return;
        }

        Schema::table('assigned_plans', function (Blueprint $table) {
            $table->dropIndex('idx_ap_client_type_active');
        });
    }
};
