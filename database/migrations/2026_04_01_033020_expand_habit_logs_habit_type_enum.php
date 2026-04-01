<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Expand habit_logs.habit_type ENUM to include entrenamiento and suplementos.
     *
     * Current:  ENUM('agua','sueno','nutricion','estres')
     * After:    ENUM('agua','sueno','entrenamiento','nutricion','suplementos','estres')
     *
     * Additive-only: existing values are preserved. 'estres' is kept for legacy
     * records written by the vanilla PHP app.
     *
     * The column also drops NOT NULL default implicitly — we keep the same
     * nullability (NOT NULL, no default) and the unique index is untouched.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE habit_logs
             MODIFY COLUMN habit_type
             ENUM('agua','sueno','entrenamiento','nutricion','suplementos','estres')
             NOT NULL"
        );
    }

    /**
     * Rollback: shrink enum back to the original four values.
     *
     * WARNING: Only safe if no rows contain 'entrenamiento' or 'suplementos'.
     * In practice this down() is a safety net for dev environments only;
     * never run it in production without auditing existing data first.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE habit_logs
             MODIFY COLUMN habit_type
             ENUM('agua','sueno','nutricion','estres')
             NOT NULL"
        );
    }
};
