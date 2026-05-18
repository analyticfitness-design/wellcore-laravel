<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ciclo_menstrual_fases — fases del ciclo menstrual + ajustes entreno/nutrición.
 *
 * Fuente: docs/audit-motor-v2/hormonal-protocols-seed.json (sub-tabla ciclo_menstrual_fases).
 * Basado en literatura Stacy Sims ROAR + Colenso-Semple + McNulty meta-analysis.
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('ciclo_menstrual_fases', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 120)->unique();
            $t->string('name', 200);
            $t->json('alternative_names')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->string('ciclo_dias_tipico', 40)->nullable();
            $t->string('ciclo_dias_rango', 40)->nullable();
            $t->unsignedTinyInteger('ciclo_assumes_dias_totales')->default(28);

            $t->json('hormonas_dominantes')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));
            $t->json('sintomas_tipicos')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->json('ajustes_entrenamiento')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));
            $t->json('ajustes_nutricion')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));
            $t->json('ajustes_sueño_recuperacion')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));
            $t->json('considerations_birth_control')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));

            $t->json('scientific_sources')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->text('legal_framing');

            $t->json('applicable_age_range')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->boolean('applicable_to_postmenopausal')->default(false);
            $t->boolean('applicable_to_pregnant')->default(false);

            $t->enum('confidence', ['high', 'moderate', 'low'])->default('moderate');
            $t->boolean('needs_gynecologist_validation')->default(true);
            $t->boolean('needs_daniel_validation')->default(true);
            $t->index('needs_gynecologist_validation', 'cmf_needs_gyn_val_idx');

            $t->json('tags')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->json('raw_data');

            $t->unsignedSmallInteger('version')->default(1);
            $t->boolean('active')->default(false)->index(); // default false hasta validación
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('ciclo_menstrual_fases');
    }
};
