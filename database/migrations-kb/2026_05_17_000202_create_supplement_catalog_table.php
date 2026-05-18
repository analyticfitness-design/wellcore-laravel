<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * supplement_catalog — suplementos legales mainstream + ergogénicos legales.
 *
 * Fuente: docs/audit-motor-v2/supplement-catalog-seed.json (28 entries).
 * Granularidad por activo (no por marca). Marcas en raw_data.commercial_products.
 *
 * NO incluye compounds hormonales prescripcionales — esos viven en hormonal_compounds.
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('supplement_catalog', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 120)->unique();
            $t->string('name', 200);
            $t->json('name_alternatives')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->string('scientific_name', 240)->nullable();

            $t->string('category', 60)->index();
            $t->string('primary_action', 80)->index();
            $t->enum('type', [
                'compound_aislado',
                'multi_ingrediente',
                'blend_pre_workout',
                'blend_post_workout',
                'blend_intra_workout',
                'alimento_funcional',
            ])->default('compound_aislado');

            $t->json('blend_components')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->json('dosis_recommended')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));
            $t->json('timing_recommended')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->string('frequency', 60)->nullable();

            $t->json('macros_per_serving')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));
            $t->boolean('serves_as_food')->default(false)->index();

            $t->json('applicable_gender')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->enum('applicable_tier_min', ['trial', 'esencial', 'metodo', 'elite', 'rise'])->default('esencial')->index();
            $t->json('applicable_objectives')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('applicable_levels')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('applicable_age_range')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->enum('evidence_level', ['muy_alta', 'alta', 'moderada', 'limitada', 'anecdotica'])
                ->default('moderada')->index();
            $t->text('evidence_summary')->nullable();

            $t->json('contraindications')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('medical_interactions')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('side_effects_common')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('side_effects_rare')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->json('synergies')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('stacks_with_common')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->enum('cost_relative', ['muy_baja', 'baja', 'media', 'alta', 'premium'])->default('media');
            $t->string('shopping_list_grouping', 60)->nullable();

            $t->text('advertencia_legal')->nullable();
            $t->json('tags')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->enum('confidence', ['high', 'moderate', 'low'])->default('high');
            $t->boolean('needs_daniel_validation')->default(false);
            $t->boolean('needs_medical_professional_review')->default(false)->index();

            $t->json('scientific_sources')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->json('raw_data');

            $t->unsignedSmallInteger('version')->default(1);
            $t->boolean('active')->default(true)->index();
            $t->timestamps();

            $t->index(['category', 'active']);
            $t->index(['applicable_tier_min', 'evidence_level']);
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('supplement_catalog');
    }
};
