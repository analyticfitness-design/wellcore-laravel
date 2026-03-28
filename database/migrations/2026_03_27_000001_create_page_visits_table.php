<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('page_visits')) {
            Schema::create('page_visits', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('session_id', 64);
                $table->string('visitor_id', 36)->nullable()->comment('Anonymous cookie UUID for cross-session tracking');
                $table->unsignedBigInteger('client_id')->nullable();
                $table->string('inscription_id')->nullable()->comment('Links to inscription if visitor later inscribes');
                $table->unsignedBigInteger('payment_id')->nullable()->comment('Links to payment if visitor later pays');

                // UTM parameters
                $table->string('utm_source', 100)->nullable();
                $table->string('utm_medium', 100)->nullable();
                $table->string('utm_campaign', 255)->nullable();
                $table->string('utm_content', 255)->nullable();
                $table->string('utm_term', 255)->nullable();

                // Visit context
                $table->string('landing_page', 500);
                $table->string('referrer', 500)->nullable();
                $table->string('ip_address', 45)->nullable()->comment('Supports IPv6');
                $table->string('user_agent', 500)->nullable();
                $table->string('country', 2)->nullable()->comment('ISO 3166-1 alpha-2 country code');
                $table->enum('device_type', ['desktop', 'mobile', 'tablet'])->nullable();

                // Conversion tracking
                $table->timestamp('converted_at')->nullable();
                $table->enum('conversion_type', ['inscription', 'payment'])->nullable();

                $table->timestamps();

                // Foreign keys with safe deletion behavior
                $table->foreign('client_id')
                    ->references('id')->on('clients')
                    ->onDelete('set null');

                // Indexes for query performance
                $table->index('session_id');
                $table->index('visitor_id');
                $table->index('client_id');
                $table->index('utm_source');
                $table->index('utm_campaign');
                $table->index(['utm_source', 'utm_campaign', 'created_at'], 'idx_page_visits_source_campaign_date');
                $table->index('created_at');
                $table->index('conversion_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
