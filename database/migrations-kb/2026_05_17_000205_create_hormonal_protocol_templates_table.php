<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * hormonal_protocol_templates — protocolos pre-armados (TRT, PCT, ergogénicos).
 *
 * Fuente: docs/audit-motor-v2/hormonal-protocols-seed.json (sub-tabla hormonal_protocols_templates).
 * Cada template combina compounds (referencias a hormonal_compounds.slug) con fases y labs schedule.
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('hormonal_protocol_templates', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 120)->unique();
            $t->string('name', 200);
            $t->text('objective')->nullable();
            $t->string('applicable_use_case', 80)->nullable();

            $t->unsignedTinyInteger('duration_weeks')->nullable();
            $t->boolean('extendible_to_long_term')->default(false);

            $t->json('compounds_combination')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('phases')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->text('pct_post_protocolo')->nullable();
            $t->json('labs_required_baseline')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('labs_schedule')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));

            $t->text('señales_emergencia')->nullable();

            $t->json('applicable_gender')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('applicable_age_range')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->enum('applicable_tier_min', ['trial', 'esencial', 'metodo', 'elite', 'rise'])->default('elite');

            $t->enum('evidence_level', ['muy_alta', 'alta', 'moderada', 'limitada', 'anecdotica'])->default('moderada');
            $t->json('scientific_sources')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->text('legal_framing');

            $t->enum('confidence', ['high', 'moderate', 'low'])->default('moderate');
            $t->boolean('needs_endocrinologist_validation')->default(true);
            $t->boolean('needs_daniel_validation')->default(true);
            $t->boolean('needs_legal_review_before_seed')->default(true);
            $t->index('needs_endocrinologist_validation', 'hpt_needs_endo_val_idx');

            $t->json('raw_data');

            $t->unsignedSmallInteger('version')->default(1);
            $t->boolean('active')->default(false)->index(); // default false hasta validación
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('hormonal_protocol_templates');
    }
};
