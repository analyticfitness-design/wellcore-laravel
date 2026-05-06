<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plan Viewer V2 · variation toggle persistence.
 *
 * Tabla aditiva-only. Persiste el flag "is_using_variant" por (cliente, ejercicio)
 * sin tocar `assigned_plans.content`, `exercises`, ni el catálogo de GIFs.
 * El gif_url original NUNCA se sobrescribe — solo cambia la elección visual.
 *
 * Down: dropIfExists. Sin destruir más datos (la tabla es nueva, no existe en
 * producción antes del deploy V2).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plan_exercise_variations')) {
            return;
        }

        Schema::create('plan_exercise_variations', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('client_id')->index();
            $t->unsignedBigInteger('exercise_id')->index();
            $t->boolean('using_variant')->default(false);
            $t->timestamps();

            $t->unique(['client_id', 'exercise_id'], 'pev_client_ex_uniq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_exercise_variations');
    }
};
