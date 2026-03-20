<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bloodwork_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('client_id');
            $table->string('test_name', 100);
            $table->decimal('value', 10, 2);
            $table->string('unit', 30);
            $table->string('reference_range', 50)->nullable();
            $table->date('test_date');
            $table->timestamps();

            $table->index('client_id', 'idx_client');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloodwork_results');
    }
};
