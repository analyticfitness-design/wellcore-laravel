<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aditiva: agrega client_id a tickets para eliminar el match por nombre
     * (que sufría IDOR por homónimos). No destruye client_name — se mantiene
     * como retro-compat para tickets antiguos hasta completar el backfill.
     */
    public function up(): void
    {
        if (! Schema::hasTable('tickets')) {
            return;
        }

        if (! Schema::hasColumn('tickets', 'client_id')) {
            Schema::table('tickets', function (Blueprint $table): void {
                $table->unsignedBigInteger('client_id')->nullable()->after('client_name');
                $table->index('client_id', 'tickets_client_id_idx');
            });
        }

        // Backfill: sólo mapeamos cuando hay un único cliente con ese nombre.
        // Si hay duplicados, dejamos client_id en NULL y lo loggeamos.
        try {
            $duplicates = DB::table('clients')
                ->select('name', DB::raw('COUNT(*) as total'))
                ->groupBy('name')
                ->having('total', '>', 1)
                ->pluck('total', 'name')
                ->toArray();

            if (! empty($duplicates)) {
                Log::warning('Tickets backfill: nombres duplicados detectados, se dejaron NULL', [
                    'duplicates_count' => count($duplicates),
                ]);
            }

            $affected = DB::update(
                'UPDATE tickets t
                 INNER JOIN clients c ON c.name = t.client_name
                 LEFT JOIN (
                     SELECT name FROM clients GROUP BY name HAVING COUNT(*) > 1
                 ) dup ON dup.name = c.name
                 SET t.client_id = c.id
                 WHERE t.client_id IS NULL AND dup.name IS NULL'
            );

            Log::info('Tickets backfill completado', ['rows_updated' => $affected]);
        } catch (Throwable $e) {
            Log::warning('Tickets backfill falló — revisar manualmente', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('tickets')) {
            return;
        }

        if (Schema::hasColumn('tickets', 'client_id')) {
            Schema::table('tickets', function (Blueprint $table): void {
                $table->dropIndex('tickets_client_id_idx');
                $table->dropColumn('client_id');
            });
        }
    }
};
