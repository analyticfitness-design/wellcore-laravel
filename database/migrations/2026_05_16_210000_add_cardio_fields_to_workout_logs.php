<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración aditiva para soporte del módulo cardio especializado (F0).
 *
 * Añade 5 columnas a `workout_logs` para permitir tracking diferenciado
 * de cardio según metodología (LISS, intervalos, Tabata, AMRAP, EMOM).
 *
 * 100% aditiva — los planes existentes siguen funcionando sin tocar nada.
 * El frontend infiere `cardio_type` desde nombre/notas/bloque de cada
 * ejercicio cuando el campo no está poblado explícitamente.
 *
 * Backup pre-migración: ver backups/2026-05-16-pre-cardio-module/
 * Rollback: down() drop columns — seguro porque solo existen post-migración.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('workout_logs', function (Blueprint $table) {
            // Tipo de cardio prescrito por el coach o inferido por frontend.
            // Valores esperados: continuous_low, continuous_moderate,
            //                    intervals, tabata, circuit, free.
            $table->string('cardio_type', 32)->nullable()->after('is_cardio');

            // Rondas planificadas vs completadas (solo intervalos/tabata/circuit).
            $table->unsignedSmallInteger('rounds_planned')->nullable()->after('duration_seconds');
            $table->unsignedSmallInteger('rounds_completed')->nullable()->after('rounds_planned');

            // Rate of Perceived Exertion (0-10) — proxy de intensidad sin pulsómetro.
            $table->unsignedTinyInteger('rpe')->nullable()->after('rounds_completed');

            // Datos estructurados adicionales: protocol_snapshot, phase_log[],
            // distance_km, heart_rate_max, calories_estimated, etc.
            // Diseñado para crecer sin migraciones futuras.
            $table->json('cardio_metadata')->nullable()->after('rpe');
        });
    }

    public function down(): void
    {
        Schema::table('workout_logs', function (Blueprint $table) {
            $table->dropColumn([
                'cardio_metadata',
                'rpe',
                'rounds_completed',
                'rounds_planned',
                'cardio_type',
            ]);
        });
    }
};
