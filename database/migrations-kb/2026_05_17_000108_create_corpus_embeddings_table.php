<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * corpus_embeddings — embeddings vectoriales para RAG retrieval.
 * OPCIONAL en MVP — el motor v2 puede correr Sprint 1-3 sin esta tabla activa.
 * Se activa en Sprint 6+ cuando se enciende el RAG.
 * Cosine similarity se hace en PHP (sin pgvector) hasta ~10K embeddings.
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.8
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('corpus_embeddings', function (Blueprint $t) {
            $t->id();
            $t->enum('source_type', [
                'methodology', 'principle', 'exercise_meta',
                'plan_template', 'doc_chunk', 'success_case',
            ]);
            $t->unsignedBigInteger('source_id');
            $t->text('chunk_text');
            $t->json('embedding');
            $t->string('model_used', 40);
            $t->unsignedInteger('token_count');
            $t->timestamp('created_at')->useCurrent();

            $t->index(['source_type', 'source_id']);
            $t->index('model_used');
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('corpus_embeddings');
    }
};
