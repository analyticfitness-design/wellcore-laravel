<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * methodologies — catálogo de metodologías que el motor v2 puede recomendar.
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.1
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('methodologies', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 64)->unique();
            $t->string('name', 120);
            $t->enum('vertical', ['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo']);
            $t->text('description');
            $t->unsignedTinyInteger('target_days_min')->nullable();
            $t->unsignedTinyInteger('target_days_max')->nullable();
            $t->enum('target_level', ['principiante', 'intermedio', 'avanzado', 'any'])->default('any');
            $t->enum('target_goal', ['hipertrofia', 'fuerza', 'perdida_grasa', 'recomposicion', 'mantenimiento', 'performance', 'any'])->default('any');
            $t->json('periodization_pattern')->nullable();
            $t->enum('status', ['active', 'experimental', 'deprecated'])->default('active');
            $t->string('created_by', 80);
            $t->string('version', 16)->default('1.0');
            $t->timestamps();

            $t->index(['vertical', 'status']);
            $t->index(['target_goal', 'target_level']);
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('methodologies');
    }
};
