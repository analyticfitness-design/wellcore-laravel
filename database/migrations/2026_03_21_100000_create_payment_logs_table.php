<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_logs')) {
            return;
        }

        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event', 100)->index();
            $table->string('reference', 100)->nullable()->index();
            $table->string('transaction_id', 100)->nullable()->index();
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->string('status', 50)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
