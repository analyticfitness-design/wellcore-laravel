<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The vanilla PHP workout_prs table has a 'value' column (NOT NULL, no default).
 * Laravel inserts do not include 'value', so we set DEFAULT 0 to avoid constraint errors.
 *
 * Using raw ALTER to avoid needing to know the column type.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workout_prs')) {
            return;
        }

        if (! Schema::hasColumn('workout_prs', 'value')) {
            return;
        }

        // Get current column definition to preserve type
        $col = collect(DB::select('SHOW COLUMNS FROM workout_prs WHERE Field = ?', ['value']))->first();

        if (! $col) {
            return;
        }

        // Already has a default — nothing to do
        if ($col->Default !== null) {
            return;
        }

        // Set DEFAULT 0, keeping the existing type and NULL constraint
        $nullable = strtoupper($col->Null) === 'YES' ? 'NULL' : 'NOT NULL';
        DB::statement("ALTER TABLE workout_prs MODIFY COLUMN `value` {$col->Type} {$nullable} DEFAULT 0");
    }

    public function down(): void
    {
        // Intentionally no-op: reverting a default is safe to skip
    }
};
