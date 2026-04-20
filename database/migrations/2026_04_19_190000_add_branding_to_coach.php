<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P4 — Mi Marca (Coach Branding)
 *
 * Additive migration: extends `coach_profiles` with branding columns
 * so each coach can showcase a personal logo + commercial name + tagline
 * to their assigned clients.
 *
 * Idempotent: every column is guarded with Schema::hasColumn.
 * Safe to re-run on envs that already ran part of it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coach_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('coach_profiles', 'nombre_comercial')) {
                $table->string('nombre_comercial', 150)->nullable()->after('bio');
            }
            if (! Schema::hasColumn('coach_profiles', 'tagline')) {
                $table->string('tagline', 250)->nullable()->after('nombre_comercial');
            }
            if (! Schema::hasColumn('coach_profiles', 'logo_url_webp')) {
                $table->string('logo_url_webp', 500)->nullable()->after('logo_url');
            }
            if (! Schema::hasColumn('coach_profiles', 'logo_path_webp')) {
                $table->string('logo_path_webp', 500)->nullable()->after('logo_url_webp');
            }
            if (! Schema::hasColumn('coach_profiles', 'logo_path_fallback')) {
                $table->string('logo_path_fallback', 500)->nullable()->after('logo_path_webp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coach_profiles', function (Blueprint $table) {
            foreach ([
                'nombre_comercial',
                'tagline',
                'logo_url_webp',
                'logo_path_webp',
                'logo_path_fallback',
            ] as $col) {
                if (Schema::hasColumn('coach_profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
