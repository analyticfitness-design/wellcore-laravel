<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * nutrition_foods — catálogo curado de alimentos crudos con macros por 100g.
 *
 * Fuente del seed: docs/audit-motor-v2/nutrition-foods-seed.json (100 entries).
 * Sirve al motor v2 para componer planes nutricionales y armar Lista de Mercado.
 *
 * Macros consultables vía columnas escalares (protein_g, carbs_g, fat_g, kcal).
 * El entry completo se preserva en raw_data por si el motor necesita campos extra.
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('nutrition_foods', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 120)->unique();
            $t->string('name', 200);
            $t->json('name_alternatives')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->string('category', 60)->index();
            $t->string('subcategory', 60)->nullable();

            // Macros por 100g crudo — escalares para queries ágiles
            $t->decimal('protein_g', 6, 2)->default(0);
            $t->decimal('carbs_g', 6, 2)->default(0);
            $t->decimal('fat_g', 6, 2)->default(0);
            $t->decimal('fiber_g', 6, 2)->nullable();
            $t->decimal('kcal', 7, 2)->default(0);

            $t->string('unit_default', 20)->default('g');
            $t->json('unit_options')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $t->json('portion_typical')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));
            $t->text('preparation_notes')->nullable();

            $t->json('availability_country')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('alternatives_protein_equivalent')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            // Dietary flags como columnas escalares para WHERE rápido
            $t->boolean('is_vegetarian')->default(false)->index();
            $t->boolean('is_vegan')->default(false)->index();
            $t->boolean('is_gluten_free')->default(false);
            $t->boolean('is_lactose_free')->default(false);
            $t->boolean('is_keto_friendly')->default(false);
            $t->boolean('is_paleo_friendly')->default(false);
            $t->string('common_allergen', 80)->nullable();

            $t->string('shopping_list_grouping', 60)->index();
            $t->string('shopping_category_ui_v1', 40)->nullable();
            $t->string('icon_emoji', 12)->nullable();

            $t->enum('cost_relative', ['muy_baja', 'baja', 'media', 'alta', 'premium'])->default('media');
            $t->smallInteger('glycemic_index')->nullable();

            $t->enum('confidence', ['high', 'moderate', 'low'])->default('high')->index();
            $t->boolean('needs_daniel_validation')->default(false)->index();

            $t->json('scientific_sources')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('tags')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            // Snapshot completo del entry JSON original
            $t->json('raw_data');

            $t->unsignedSmallInteger('version')->default(1);
            $t->boolean('active')->default(true)->index();
            $t->timestamps();

            $t->index(['category', 'active']);
            $t->index(['shopping_list_grouping', 'active']);
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('nutrition_foods');
    }
};
