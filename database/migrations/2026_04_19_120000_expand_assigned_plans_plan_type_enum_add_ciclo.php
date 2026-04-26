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

        DB::statement("SET SESSION sql_mode = ''");
        DB::statement("
            ALTER TABLE assigned_plans
            MODIFY COLUMN plan_type ENUM(
                'entrenamiento','nutricion','habitos','suplementacion','ciclo'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE assigned_plans
            MODIFY COLUMN plan_type ENUM(
                'entrenamiento','nutricion','habitos','suplementacion'
            ) NOT NULL
        ");
    }
};
