<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aditiva: agrega client_note (texto descriptivo del cliente sobre la foto).
 * El cliente puede dictar/escribir lo que comió. Se muestra al coach.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('food_photos') || Schema::hasColumn('food_photos', 'client_note')) {
            return;
        }

        Schema::table('food_photos', function (Blueprint $table) {
            $table->text('client_note')->nullable()->after('coach_note');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('food_photos') || ! Schema::hasColumn('food_photos', 'client_note')) {
            return;
        }

        Schema::table('food_photos', function (Blueprint $table) {
            $table->dropColumn('client_note');
        });
    }
};
