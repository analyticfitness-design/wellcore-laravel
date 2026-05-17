<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * exercise_metadata — los 265 ejercicios enriquecidos.
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.3
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('exercise_metadata', function (Blueprint $t) {
            $t->id();
            $t->string('alias', 80)->unique();
            $t->string('name_canonical', 160);
            $t->string('muscle_primary', 40);
            $t->string('muscle_secondary', 160)->nullable();
            $t->json('equipment_required')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('equipment_substitutes')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->enum('level_min', ['principiante', 'intermedio', 'avanzado'])->default('principiante');
            $t->enum('compound_isolation', ['compound', 'isolation']);
            $t->enum('movement_pattern', [
                'push_horizontal', 'push_vertical', 'pull_horizontal', 'pull_vertical',
                'squat', 'hinge', 'lunge', 'core', 'carry',
                'cardio_steady', 'cardio_intervals', 'other',
            ])->nullable();
            $t->json('contraindications')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->text('common_mistakes')->nullable();
            $t->json('coaching_cues')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->json('variations')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $t->timestamp('gif_url_verified_at')->nullable();
            $t->enum('gif_url_status', ['ok', 'broken', 'missing', 'unknown'])->default('unknown');
            $t->timestamps();

            $t->index('muscle_primary');
            $t->index(['compound_isolation', 'level_min']);
            $t->index('movement_pattern');
            $t->index('gif_url_status');
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('exercise_metadata');
    }
};
