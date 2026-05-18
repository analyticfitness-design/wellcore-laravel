<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * supplement_stacks — combinaciones pre-armadas de suplementos.
 *
 * Fuente: docs/audit-motor-v2/supplement-stacks-seed.json (15 stacks).
 * Cada stack referencia slugs de supplement_catalog en components_*.
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('supplement_stacks', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 120)->unique();
            $t->string('name', 200);
            $t->string('name_short', 80)->nullable();
            $t->text('objective')->nullable();

            $t->json('applicable_objectives')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('applicable_genders')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->enum('applicable_tier_min', ['trial', 'esencial', 'metodo', 'elite', 'rise'])->default('esencial')->index();
            $t->json('applicable_levels')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('applicable_age_range')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('applicable_special_conditions')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            // Components — referencias a supplement_catalog.slug
            $t->json('components_essential')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('components_recommended')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('components_optional')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->unsignedTinyInteger('total_components_essential')->default(0);
            $t->unsignedTinyInteger('total_components_recommended')->default(0);
            $t->unsignedTinyInteger('total_components_optional')->default(0);
            $t->unsignedTinyInteger('total_components_max_stack')->default(0);

            $t->json('stack_interactions_internal')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('client_interactions_externas')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->json('expected_outcomes')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->text('expected_timeline_resultados')->nullable();

            $t->unsignedInteger('approximate_monthly_cost_cop')->nullable();
            $t->string('approximate_monthly_cost_range_cop', 80)->nullable();
            $t->text('cost_breakdown_note')->nullable();

            $t->json('observed_in_real_clients')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('alternatives_if_components_unavailable')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->text('scientific_rationale')->nullable();
            $t->json('scientific_sources')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->text('legal_advertencia')->nullable();
            $t->json('tags')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->enum('confidence', ['high', 'moderate', 'low'])->default('high');
            $t->text('confidence_reason')->nullable();
            $t->boolean('needs_daniel_validation')->default(false);
            $t->boolean('needs_medical_review')->default(false);

            $t->json('raw_data');

            $t->unsignedSmallInteger('version')->default(1);
            $t->boolean('active')->default(true)->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('supplement_stacks');
    }
};
