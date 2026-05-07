<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aditiva — agrega is_client_reply BOOL para distinguir notas del coach
 * vs respuestas del cliente en el thread de la foto.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('progress_photo_notes')) {
            return;
        }

        Schema::table('progress_photo_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('progress_photo_notes', 'is_client_reply')) {
                $table->boolean('is_client_reply')->default(false)->after('note_text');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('progress_photo_notes')) {
            return;
        }

        Schema::table('progress_photo_notes', function (Blueprint $table) {
            if (Schema::hasColumn('progress_photo_notes', 'is_client_reply')) {
                $table->dropColumn('is_client_reply');
            }
        });
    }
};
