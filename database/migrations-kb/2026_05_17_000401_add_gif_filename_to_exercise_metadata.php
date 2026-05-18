<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega gif_filename opcional a exercise_metadata.
 *
 * Si está seteado, gifUrl() lo usa en lugar de "{alias}.gif".
 * Resuelve el caso donde el alias canónico difiere del nombre real del repo:
 *   alias canónico:  curl-biceps-barra-z
 *   repo real:       curl-biceps-barra-ez.gif
 *
 * Aditiva: no rompe seeders existentes.
 * Solo afecta wellcore_kb local. NO toca producción.
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->table('exercise_metadata', function (Blueprint $t) {
            $t->string('gif_filename', 120)->nullable()->after('alias');
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->table('exercise_metadata', function (Blueprint $t) {
            $t->dropColumn('gif_filename');
        });
    }
};
