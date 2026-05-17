<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * plan_templates_local — templates listos por perfil.
 * NO confundir con plan_templates de producción (esa pertenece al PlanTemplate model coach).
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.5
 * Memoria autoritativa: source NUNCA puede ser 'ai_generated' (ver feedback_no_ai_plan_generator_corpus).
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('plan_templates_local', function (Blueprint $t) {
            $t->id();
            $t->string('name', 160);
            $t->enum('vertical', ['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo']);
            $t->json('target_profile_json');
            $t->longText('structure_json');
            $t->enum('source', ['curated_literature', 'from_real_client', 'manual_daniel', 'manual_coach']);
            $t->unsignedTinyInteger('quality_score')->default(50);
            $t->unsignedInteger('times_used')->default(0);
            $t->timestamp('last_used_at')->nullable();
            $t->string('created_by', 80);
            $t->string('version', 16)->default('1.0');
            $t->enum('status', ['active', 'experimental', 'deprecated'])->default('active');
            $t->timestamps();

            $t->index(['vertical', 'status', 'quality_score']);
            $t->index('source');
            $t->index('times_used');
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('plan_templates_local');
    }
};
