<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplement_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->date('log_date');
            $table->string('supplement_name', 100);
            $table->string('timing', 20);
            $table->boolean('taken')->default(false);
            $table->timestamps();

            $table->unique(['client_id', 'log_date', 'supplement_name', 'timing'], 'suppl_log_unique');
            $table->index(['client_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplement_logs');
    }
};
