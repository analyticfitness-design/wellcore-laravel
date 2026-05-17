<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * lint_rules — el catalogo del linter pre-INSERT (DB-driven, hot-reload sin redeploy).
 * VALIDATE stage las carga dinamicamente.
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.7 y docs/wellcore-engine-v2/06-lint-rules.md
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('lint_rules', function (Blueprint $t) {
            $t->id();
            $t->string('code', 80)->unique();
            $t->enum('vertical', ['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo'])->nullable();
            $t->enum('severity', ['error', 'warning', 'info']);
            $t->text('description');
            $t->enum('check_type', ['schema', 'heuristic', 'external_head', 'sql', 'llm_review']);
            $t->json('check_definition_json');
            $t->text('fix_hint_template');
            $t->boolean('enabled')->default(true);
            $t->boolean('auto_fix_available')->default(false);
            $t->string('created_by', 80);
            $t->timestamps();

            $t->index(['enabled', 'severity']);
            $t->index('vertical');
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('lint_rules');
    }
};
