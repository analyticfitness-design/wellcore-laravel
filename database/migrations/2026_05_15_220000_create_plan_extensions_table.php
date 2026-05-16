<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit log de extensiones manuales de membresía hechas desde el panel admin/coach.
 *
 * Aditiva (cumple ADR-0003) — solo CREATE TABLE, sin tocar tablas existentes.
 * Snapshot del rol del actor para sobrevivir degradaciones futuras del Admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_extensions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('actor_admin_id')->index();
            $table->string('actor_role', 32);
            $table->date('previous_expires_at')->nullable();
            $table->date('new_expires_at');
            $table->text('notes')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['client_id', 'created_at']);
            $table->index(['actor_admin_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_extensions');
    }
};
