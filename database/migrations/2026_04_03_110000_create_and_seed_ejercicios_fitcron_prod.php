<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Create table only if it doesn't exist yet
        if (! Schema::hasTable('ejercicios_fitcron')) {
            Schema::create('ejercicios_fitcron', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('nombre');
                $table->string('tipo')->nullable();
                $table->string('grupo_muscular')->nullable();
                $table->text('musculos_involucrados')->nullable();
                $table->string('equipamiento')->nullable();
                $table->integer('dificultad')->nullable();
                $table->string('gif_url', 500)->nullable();
                $table->string('gif_filename')->nullable();
                $table->string('gif_path')->nullable();
                $table->string('gif_path_sin_fondo')->nullable();
                $table->boolean('sin_fondo_listo')->default(false);
                $table->boolean('descargado')->default(false);
                $table->string('video_url', 500)->nullable();
                $table->timestamps();
            });
        }

        // Seed unique exercise names from active assigned_plans
        $plans = DB::table('assigned_plans')->whereNotNull('content')->get(['content']);
        $names = [];

        foreach ($plans as $plan) {
            $d = json_decode($plan->content, true) ?? [];
            $dias = $d['dias'] ?? $d['days'] ?? [];

            foreach ($dias as $dia) {
                if (! is_array($dia)) {
                    continue;
                }
                $exs = $dia['ejercicios'] ?? $dia['exercises'] ?? $dia['ejercicios_dia'] ?? [];
                foreach ($exs as $ex) {
                    if (! is_array($ex)) {
                        continue;
                    }
                    $n = $ex['nombre'] ?? $ex['name'] ?? $ex['exercise'] ?? '';
                    if ($n) {
                        $names[] = trim($n);
                    }
                }
            }
        }

        $now = now();
        foreach (array_unique($names) as $nombre) {
            $slug = Str::slug($nombre);
            if (! $slug) {
                continue;
            }

            DB::table('ejercicios_fitcron')->insertOrIgnore([
                'slug'       => $slug,
                'nombre'     => $nombre,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Intentionally a no-op — dropping exercise data is destructive
    }
};
