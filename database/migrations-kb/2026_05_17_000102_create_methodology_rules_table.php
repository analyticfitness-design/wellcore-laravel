<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * methodology_rules — filtros de elegibilidad: SELECT stage los consulta.
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.2
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('methodology_rules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('methodology_id')->constrained('methodologies')->cascadeOnDelete();
            $t->enum('rule_type', ['hard_filter', 'soft_filter', 'preference']);
            $t->json('applies_when_json');
            $t->decimal('weight', 4, 2)->default(1.00);
            $t->text('explanation');
            $t->timestamps();

            $t->index('methodology_id');
            $t->index('rule_type');
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('methodology_rules');
    }
};
