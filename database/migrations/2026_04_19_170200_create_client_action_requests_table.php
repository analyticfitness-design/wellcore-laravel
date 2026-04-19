<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_action_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('coach_id')->index();
            $table->string('coach_name', 150);
            $table->unsignedBigInteger('client_id')->index();
            $table->string('client_name', 150);
            $table->string('action', 20);
            $table->text('reason');
            $table->string('status', 20)->default('pendiente')->index();
            $table->text('admin_notas')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_action_requests');
    }
};
