<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('platform', ['meta', 'google', 'tiktok', 'email'])->default('meta');
            $table->enum('status', ['active', 'paused', 'ended'])->default('active');
            $table->unsignedBigInteger('budget_cop')->default(0);
            $table->unsignedBigInteger('spent_cop')->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedInteger('leads')->default(0);
            $table->unsignedInteger('sales')->default(0);
            $table->unsignedBigInteger('revenue_cop')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('daily_stats')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
