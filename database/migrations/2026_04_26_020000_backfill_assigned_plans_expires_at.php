<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assigned_plans')) {
            return;
        }

        // Backfill todos los planes activos sin expires_at.
        // Usa valid_from o created_at como base + 30 días.
        DB::statement('
            UPDATE assigned_plans
            SET expires_at = DATE_ADD(
                COALESCE(valid_from, created_at, NOW()),
                INTERVAL 30 DAY
            )
            WHERE expires_at IS NULL
              AND active = 1
        ');
    }

    public function down(): void
    {
        // No revertir — sería destructivo sobre datos de producción.
    }
};
