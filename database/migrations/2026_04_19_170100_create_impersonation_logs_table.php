<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impersonation_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('actor_type', 20);
            $table->unsignedBigInteger('actor_id')->index();
            $table->string('actor_name', 150);
            $table->unsignedBigInteger('target_client_id')->index();
            $table->string('target_client_name', 150);
            $table->string('token', 80)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impersonation_logs');
    }
};
