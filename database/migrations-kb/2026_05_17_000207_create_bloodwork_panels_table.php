<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * bloodwork_panels — paneles de análisis de sangre agrupados por relevancia.
 *
 * Fuente: docs/audit-motor-v2/hormonal-protocols-seed.json (sub-tabla bloodwork_panels).
 * Paneles: hormonal masculino, hormonal femenino, atleta BB, metabólico/diabetes, baseline.
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('bloodwork_panels', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 120)->unique();
            $t->string('name', 200);
            $t->text('applicable_to')->nullable();

            $t->json('tests_incluidos')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->string('frecuencia_recomendada', 240)->nullable();

            $t->text('costo_estimado_colombia_cop')->nullable();
            $t->text('costo_estimado_mexico_mxn')->nullable();
            $t->json('laboratorios_recomendados_co')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->text('interpretation_general')->nullable();

            $t->json('scientific_sources')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->text('legal_framing');

            $t->json('applicable_gender')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('applicable_age_range')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->enum('confidence', ['high', 'moderate', 'low'])->default('high');
            $t->boolean('needs_endocrinologist_validation')->default(false);
            $t->boolean('needs_daniel_validation')->default(false);

            $t->json('raw_data');

            $t->unsignedSmallInteger('version')->default(1);
            $t->boolean('active')->default(true)->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('bloodwork_panels');
    }
};
