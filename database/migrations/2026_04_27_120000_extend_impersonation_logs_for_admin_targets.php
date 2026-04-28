<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('impersonation_logs', function (Blueprint $table) {
            $table->string('target_type', 20)->default('client')->after('actor_name');
            $table->unsignedBigInteger('target_id')->nullable()->after('target_type')->index();
            $table->string('target_name', 150)->nullable()->after('target_id');

            $table->string('via_actor_type', 20)->nullable()->after('actor_name');
            $table->unsignedBigInteger('via_actor_id')->nullable()->after('via_actor_type');
            $table->string('via_actor_name', 150)->nullable()->after('via_actor_id');

            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::table('impersonation_logs', function (Blueprint $table) {
            $table->dropIndex(['target_type', 'target_id']);
            $table->dropIndex(['target_id']);
            $table->dropColumn([
                'target_type', 'target_id', 'target_name',
                'via_actor_type', 'via_actor_id', 'via_actor_name',
            ]);
        });
    }
};
