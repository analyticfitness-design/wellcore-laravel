<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('impersonation_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('target_client_id')->nullable()->change();
            $table->string('target_client_name', 150)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('impersonation_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('target_client_id')->nullable(false)->change();
            $table->string('target_client_name', 150)->nullable(false)->change();
        });
    }
};
