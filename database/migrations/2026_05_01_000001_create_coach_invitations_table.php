<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_invitations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedInteger('coach_id');   // admins.id is INT UNSIGNED
            $table->char('code', 32)->unique();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('plan');
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('COP');
            $table->string('subject');
            $table->text('intro_message')->nullable();
            $table->string('cta_label', 100)->default('Comenzar mi plan ahora');
            $table->string('wompi_payment_link_id', 100)->nullable();
            $table->text('wompi_payment_link_url')->nullable();
            $table->string('wompi_reference', 40)->nullable()->unique();
            $table->unsignedInteger('payment_id')->nullable();  // payments.id is INT UNSIGNED
            $table->unsignedInteger('client_id')->nullable();   // clients.id is INT UNSIGNED
            $table->string('status', 20)->default('sent');
            $table->unsignedTinyInteger('resend_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('coach_id')->references('id')->on('admins');
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();

            $table->index(['coach_id', 'status'], 'ci_coach_status_idx');
            $table->index(['email', 'status'], 'ci_email_status_idx');
            $table->index('expires_at', 'ci_expires_idx');
            $table->index('created_at', 'ci_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_invitations');
    }
};
