<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega expires_at a assigned_plans para el sistema de lock por 30 días.
     * Idempotente — si la columna ya existe, no falla.
     * Backfill: para filas existentes con valid_from, calcula valid_from + 30 días.
     */
    public function up(): void
    {
        if (! Schema::hasTable('assigned_plans')) {
            return;
        }

        if (! Schema::hasColumn('assigned_plans', 'expires_at')) {
            Schema::table('assigned_plans', function (Blueprint $table) {
                $table->date('expires_at')->nullable()->after('valid_from')->index();
            });
        }

        // Backfill: planes existentes con valid_from pero sin expires_at
        // Calcula expires_at = valid_from + 30 días (solo para planes mensuales)
        DB::table('assigned_plans')
            ->whereNotNull('valid_from')
            ->whereNull('expires_at')
            ->whereIn('plan_type', ['esencial', 'metodo', 'elite'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('assigned_plans')
                        ->where('id', $row->id)
                        ->update([
                            'expires_at' => date('Y-m-d', strtotime($row->valid_from.' +30 days')),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('assigned_plans', 'expires_at')) {
            Schema::table('assigned_plans', function (Blueprint $table) {
                $table->dropIndex(['expires_at']);
                $table->dropColumn('expires_at');
            });
        }
    }
};
