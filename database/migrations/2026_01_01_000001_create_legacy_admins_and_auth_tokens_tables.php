<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the legacy `admins` and `auth_tokens` tables if they don't exist.
 *
 * These tables originate from the vanilla PHP app that WellCore Laravel shares
 * a database with (Strangler Fig pattern). In production the tables are created
 * by the vanilla app; this migration ensures they exist in any fresh Laravel
 * test / development environment.
 *
 * SAFE: Uses hasTable() guards — will never destroy existing data.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username', 50)->unique();
                $table->string('email', 150)->nullable()->index();
                $table->string('whatsapp', 30)->nullable();
                $table->boolean('must_change_password')->default(true);
                $table->json('onboarding_state')->nullable();
                $table->timestamp('password_changed_at')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamp('last_login_at')->nullable();
                $table->string('password_hash', 255);
                $table->string('name', 255)->nullable();
                $table->enum('role', ['coach', 'admin', 'jefe', 'superadmin', 'coaches', 'clientes', 'coach_manager'])
                      ->nullable()
                      ->default('coach');
                $table->timestamp('created_at')->useCurrent()->nullable();
            });
        }

        if (! Schema::hasTable('auth_tokens')) {
            Schema::create('auth_tokens', function (Blueprint $table) {
                $table->increments('id');
                $table->enum('user_type', ['client', 'admin'])->index();
                $table->unsignedInteger('user_id')->index();
                $table->char('token', 64)->unique();
                $table->char('fingerprint', 64)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('expires_at');
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('created_at')->useCurrent()->nullable();
            });
        }
    }

    public function down(): void
    {
        // Intentionally left empty — never drop legacy tables.
    }
};
