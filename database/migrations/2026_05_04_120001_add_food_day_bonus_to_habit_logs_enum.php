<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expand habit_logs.habit_type ENUM to include 'food_day_bonus'.
     *
     * Current:  ENUM('agua','sueno','entrenamiento','nutricion','suplementos','estres')
     * After:    ENUM('agua','sueno','entrenamiento','nutricion','suplementos','estres','food_day_bonus')
     *
     * Aditivo: valores existentes preservados. Patrón replicado de
     * 2026_04_01_033020_expand_habit_logs_habit_type_enum.php.
     */
    public function up(): void
    {
        if (! Schema::hasTable('habit_logs')) {
            return;
        }

        DB::statement(
            "ALTER TABLE habit_logs
             MODIFY COLUMN habit_type
             ENUM('agua','sueno','entrenamiento','nutricion','suplementos','estres','food_day_bonus')
             NOT NULL"
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('habit_logs')) {
            return;
        }

        DB::statement(
            "ALTER TABLE habit_logs
             MODIFY COLUMN habit_type
             ENUM('agua','sueno','entrenamiento','nutricion','suplementos','estres')
             NOT NULL"
        );
    }
};
