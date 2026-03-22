<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Index on assigned_plans (assigned_by).
 *
 * Covers the ClientKanban query that filters by coach ID:
 *   WHERE assigned_by = ?
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

        if (! Schema::hasColumn('assigned_plans', 'assigned_by')) {
            return;
        }

        $existingKeys = collect(DB::select('SHOW INDEX FROM assigned_plans'))
            ->pluck('Key_name')
            ->unique()
            ->values();

        if ($existingKeys->contains('idx_aplans_assigned_by')) {
            return;
        }

        Schema::table('assigned_plans', function (Blueprint $table) {
            $table->index('assigned_by', 'idx_aplans_assigned_by');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('assigned_plans')) {
            return;
        }

        $existingKeys = collect(DB::select('SHOW INDEX FROM assigned_plans'))
            ->pluck('Key_name')
            ->unique()
            ->values();

        if (! $existingKeys->contains('idx_aplans_assigned_by')) {
            return;
        }

        Schema::table('assigned_plans', function (Blueprint $table) {
            $table->dropIndex('idx_aplans_assigned_by');
        });
    }
};
