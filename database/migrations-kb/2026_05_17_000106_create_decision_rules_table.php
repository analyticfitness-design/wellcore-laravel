<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * decision_rules — input pattern → metodología recomendada con confidence.
 * SELECT stage las usa para rankear candidatas más allá del filtro hard/soft.
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.6
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('decision_rules', function (Blueprint $t) {
            $t->id();
            $t->string('name', 160);
            $t->json('when_json');
            $t->foreignId('then_methodology_id')->constrained('methodologies')->cascadeOnDelete();
            $t->decimal('confidence', 3, 2);
            $t->text('rationale');
            $t->string('author', 80);
            $t->enum('status', ['active', 'experimental', 'deprecated'])->default('active');
            $t->unsignedInteger('times_fired')->default(0);
            $t->timestamps();

            $t->index('then_methodology_id');
            $t->index(['status', 'confidence']);
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('decision_rules');
    }
};
