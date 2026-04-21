<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aditiva: agrega timezone al cliente para que los check-ins y cortes
     * diarios respeten la hora local de cada usuario (México, Colombia, etc.)
     * en lugar de forzar America/Bogota.
     */
    public function up(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        if (! Schema::hasColumn('clients', 'timezone')) {
            Schema::table('clients', function (Blueprint $table): void {
                $table->string('timezone', 50)->nullable()->default('America/Bogota')->after('email');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        if (Schema::hasColumn('clients', 'timezone')) {
            Schema::table('clients', function (Blueprint $table): void {
                $table->dropColumn('timezone');
            });
        }
    }
};
