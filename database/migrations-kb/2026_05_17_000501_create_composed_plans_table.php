<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * composed_plans — Stage 5 PERSIST del motor v2.
 *
 * Audit trail de cada plan generado por el ComposeEngine. NO reemplaza la tabla
 * de producción `assigned_plans` — esta es solo el snapshot KB-local antes de
 * la subida manual a producción.
 *
 * Cada row registra:
 *   - input: profile_json + methodology_slug
 *   - output: plan_json (el JSON listo para subir)
 *   - quality: lint_result_json (pre+post fix), fixes_applied_json
 *   - meta: status, export_path, notes
 *
 * Solo wellcore_kb local. NO toca producción.
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->create('composed_plans', function (Blueprint $t) {
            $t->id();
            $t->string('client_handle', 120)->nullable()->index();
            $t->string('plan_type', 32);
            $t->string('methodology_slug', 64);
            $t->json('profile_json');
            $t->longText('plan_json');
            $t->json('lint_result_pre_json')->nullable();
            $t->json('lint_result_post_json')->nullable();
            $t->json('fixes_applied_json')->nullable();
            $t->unsignedSmallInteger('violations_before')->default(0);
            $t->unsignedSmallInteger('violations_after')->default(0);
            $t->enum('status', ['composed', 'validated', 'exported', 'rejected'])->default('composed');
            $t->string('export_path', 255)->nullable();
            $t->text('notes')->nullable();
            $t->float('compose_duration_ms')->nullable();
            $t->float('lint_duration_ms')->nullable();
            $t->string('created_by', 80)->default('motor-v2-sprint-5');
            $t->timestamps();

            $t->index(['methodology_slug', 'status']);
            $t->index(['plan_type', 'created_at']);
            $t->index('status');
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->dropIfExists('composed_plans');
    }
};
