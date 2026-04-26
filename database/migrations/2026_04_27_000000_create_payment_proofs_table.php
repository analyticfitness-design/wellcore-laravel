<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained('admins')->cascadeOnDelete();
            $table->string('client_email', 255);
            $table->string('client_name', 255);
            $table->enum('plan', ['rise', 'esencial', 'metodo', 'elite', 'presencial']);
            $table->decimal('amount', 10, 2)->nullable();
            $table->char('currency', 3)->default('COP');
            $table->string('payment_method', 50)->nullable();
            $table->string('file_path', 500);
            $table->string('file_disk', 20)->default('payment_proofs');
            $table->string('file_mime', 50);
            $table->unsignedInteger('file_size');
            $table->text('coach_note')->nullable();
            $table->enum('status', ['pendiente', 'aprobado', 'rechazado', 'expirado'])->default('pendiente');
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->foreignId('coach_invitation_id')->nullable()->constrained('coach_invitations')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['coach_id', 'status']);
            $table->index('client_email');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }
};
