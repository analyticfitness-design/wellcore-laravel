<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ejercicio_videos', function (Blueprint $table) {
            $table->id();
            $table->string('fitcron_slug', 255)->index();
            $table->string('nombre_display', 255);
            $table->string('youtube_url', 500);
            $table->text('keywords')->nullable()->comment('JSON array de palabras clave para matching');
            $table->tinyInteger('active')->default(1)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ejercicio_videos');
    }
};
