<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('coach_id')->index();
            $table->string('coach_name', 150);
            $table->unsignedBigInteger('client_id')->index();
            $table->string('client_name', 150);
            $table->string('plan_type', 30)->index();
            $table->string('status', 30)->default('borrador')->index();
            $table->json('datos_generales');
            $table->json('plan_entrenamiento');
            $table->json('plan_nutricional')->nullable();
            $table->json('plan_habitos')->nullable();
            $table->json('plan_ciclo')->nullable();
            $table->text('notas_coach')->nullable();
            $table->text('admin_notas')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->index(['coach_id', 'status']);
            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_tickets');
    }
};
