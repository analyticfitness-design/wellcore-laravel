<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate wompi_reference rows before adding the unique constraint.
        // Keep the row with the highest id (most recent) when duplicates exist.
        DB::statement('
            DELETE p1 FROM payments p1
            INNER JOIN payments p2
            WHERE p1.id < p2.id
              AND p1.wompi_reference = p2.wompi_reference
              AND p1.wompi_reference IS NOT NULL
        ');

        Schema::table('payments', function (Blueprint $table) {
            $table->unique('wompi_reference', 'ux_payments_wompi_reference');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('ux_payments_wompi_reference');
        });
    }
};
