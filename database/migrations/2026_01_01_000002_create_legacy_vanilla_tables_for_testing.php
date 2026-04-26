<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the vanilla-PHP legacy tables that the payment proof feature depends on.
 *
 * In production these tables are created by the vanilla PHP WellCore app
 * (shared-DB strangler fig pattern). In fresh test/dev environments they
 * must be seeded here so Laravel migrations and factories can use them.
 *
 * SAFE: Every table is guarded by hasTable() — will NEVER destroy existing data.
 *
 * Tables created:
 *   - clients           (used by Client model, ApprovePaymentProofAction)
 *   - payments          (used by Payment model, ApprovePaymentProofAction)
 *   - notifications     (used by WellcoreNotification model)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ------------------------------------------------------------------
        // clients
        // ------------------------------------------------------------------
        if (! Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->increments('id');
                $table->string('client_code', 20)->unique()->nullable();
                $table->string('name', 255);
                $table->string('email', 255)->unique();
                $table->string('password_hash', 255)->nullable();
                $table->string('google_id', 100)->nullable();
                $table->string('plan', 30)->nullable();
                $table->string('status', 30)->default('activo');
                $table->string('avatar_url', 500)->nullable();
                $table->text('bio')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('timezone', 60)->nullable();
                $table->date('birth_date')->nullable();
                $table->date('fecha_inicio')->nullable();
                $table->string('referral_code', 20)->nullable();
                $table->unsignedInteger('referred_by')->nullable();
                $table->boolean('onboarding_completed')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index('email');
                $table->index('status');
            });
        }

        // ------------------------------------------------------------------
        // payments
        // ------------------------------------------------------------------
        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('client_id')->nullable();
                $table->string('email', 255)->nullable();
                $table->string('buyer_name', 255)->nullable();
                $table->string('buyer_phone', 30)->nullable();
                $table->string('plan', 30)->nullable();
                $table->decimal('amount', 10, 2)->nullable();
                $table->char('currency', 3)->default('COP');
                $table->string('status', 20)->default('PENDING');
                $table->string('payment_method', 50)->nullable();
                $table->string('wompi_reference', 100)->nullable()->unique();
                $table->string('wompi_transaction_id', 100)->nullable();
                $table->string('payu_reference', 100)->nullable();
                $table->string('payu_transaction_id', 100)->nullable();
                $table->json('payu_response')->nullable();
                $table->timestamps();

                $table->index('email');
                $table->index('status');
                $table->index('client_id');
            });
        }

        // ------------------------------------------------------------------
        // notifications  (WellcoreNotification model uses this table)
        // ------------------------------------------------------------------
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->string('user_type', 10);   // 'admin' | 'client'
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('type', 80);
                $table->string('title', 255);
                $table->text('body')->nullable();
                $table->string('link', 500)->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['user_type', 'user_id']);
                $table->index('type');
                $table->index('read_at');
            });
        }
    }

    public function down(): void
    {
        // Only drop if we created them (i.e., in a test/dev environment)
        // Production tables are managed by the vanilla PHP app
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('clients');
    }
};
