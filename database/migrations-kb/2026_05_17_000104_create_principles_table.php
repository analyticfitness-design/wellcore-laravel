<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * principles — principios de coaching reutilizables (sobrecarga progresiva, técnica primero, etc.)
 * COMPOSE stage los inyecta en notas_coach, tips[], notas de ejercicio.
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.4
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('principles', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 64)->unique();
            $t->string('name', 120);
            $t->enum('vertical', ['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo']);
            $t->string('description_short', 280);
            $t->text('description_long');
            $t->text('when_to_apply');
            $t->text('example_usage')->nullable();
            $t->json('tags');
            $t->enum('status', ['active', 'experimental', 'deprecated'])->default('active');
            $t->string('created_by', 80);
            $t->timestamps();

            $t->index(['vertical', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('principles');
    }
};
