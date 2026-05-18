<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * hormonal_compounds — activos farmacológicos hormonales/ergogénicos.
 *
 * Fuente: docs/audit-motor-v2/hormonal-protocols-seed.json (sub-tabla hormonal_compounds_catalog).
 *
 * FRAMING LEGAL CRÍTICO: WellCore NO prescribe. Esta tabla DOCUMENTA protocolos
 * que el cliente Elite ya tiene prescritos por su endocrinólogo externo.
 * Cada entry lleva legal_framing explícito. NO se listan marcas comerciales.
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('hormonal_compounds', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 120)->unique();
            $t->string('name', 200);
            $t->json('name_alternatives')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->string('scientific_name', 240)->nullable();

            // category: vocabulario controlado pero flexible (string en lugar de enum
            // para no requerir migration cada vez que se descubre un nuevo activo)
            $t->string('category', 80)->index();
            $t->string('primary_action', 80);
            $t->string('use_case_primary', 80);
            $t->json('use_case_secondary')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->string('via_administracion', 60)->nullable();
            $t->json('via_options_clinicas')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->json('farmacocinetica')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));
            $t->json('dosis_rango_clinico_trt')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));
            $t->json('dosis_rango_ergogenico_off_label')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));
            $t->json('objetivo_serico_testosterona_total')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));

            $t->json('labs_monitoreo_obligatorios')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->string('lab_frecuencia', 240)->nullable();

            $t->json('estradiol_management')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));
            $t->json('hematocrit_management')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));

            $t->json('efectos_terapeuticos_esperados')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('efectos_secundarios_comunes')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('efectos_secundarios_raros')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->json('contraindications_absolutas')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('contraindications_relativas')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('medical_interactions')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('señales_alerta_emergencia_medica')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->json('applicable_gender')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('applicable_age_range_clinico')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->unsignedTinyInteger('applicable_age_range_ergogenico_no_recomendado_under')->nullable();

            $t->enum('evidence_level_terapeutico', ['muy_alta', 'alta', 'moderada', 'limitada', 'anecdotica'])->default('moderada');
            $t->enum('evidence_level_ergogenico', ['muy_alta', 'alta', 'moderada', 'limitada', 'anecdotica'])->default('moderada');
            $t->text('evidence_summary')->nullable();

            $t->json('scientific_sources')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            // LEGAL FRAMING — obligatorio en cada entry
            $t->text('legal_framing');
            $t->text('legal_status_colombia')->nullable();
            $t->text('legal_status_otros_paises_latam')->nullable();

            $t->enum('confidence', ['high', 'moderate', 'low'])->default('moderate');
            $t->boolean('needs_endocrinologist_validation')->default(true)->index();
            $t->boolean('needs_daniel_validation')->default(true);

            $t->json('tags')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->json('raw_data');

            $t->unsignedSmallInteger('version')->default(1);
            $t->boolean('active')->default(false)->index(); // default false — requiere validación médica
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('hormonal_compounds');
    }
};
