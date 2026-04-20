<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2.3 Audit Log — additive migration.
 *
 * If audit_logs does not exist: create it with the target schema (actor_*,
 * target_*, diff, ip, user_agent, created_at).
 *
 * If audit_logs already exists (old legacy schema with user_*/model_*/old_values/
 * new_values), only ADD the missing columns so both Services (AuditService
 * using user_*) and the new Auditable trait (using actor_*) can coexist
 * until we consolidate. NEVER drops or renames columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('actor_type', 20)->nullable();
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('actor_name', 150)->nullable();
                $table->string('action', 50);
                $table->string('target_type', 50)->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->string('target_label', 255)->nullable();
                $table->json('diff')->nullable();
                $table->string('ip', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent();

                $table->index(['actor_type', 'actor_id', 'created_at'], 'audit_logs_actor_idx');
                $table->index(['target_type', 'target_id'], 'audit_logs_target_idx');
                $table->index('action', 'audit_logs_action_idx');
            });

            return;
        }

        // Table exists — additive only.
        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'actor_type')) {
                $table->string('actor_type', 20)->nullable()->after('id');
            }
            if (! Schema::hasColumn('audit_logs', 'actor_id')) {
                $table->unsignedBigInteger('actor_id')->nullable()->after('actor_type');
            }
            if (! Schema::hasColumn('audit_logs', 'actor_name')) {
                $table->string('actor_name', 150)->nullable()->after('actor_id');
            }
            if (! Schema::hasColumn('audit_logs', 'target_type')) {
                $table->string('target_type', 50)->nullable();
            }
            if (! Schema::hasColumn('audit_logs', 'target_id')) {
                $table->unsignedBigInteger('target_id')->nullable();
            }
            if (! Schema::hasColumn('audit_logs', 'target_label')) {
                $table->string('target_label', 255)->nullable();
            }
            if (! Schema::hasColumn('audit_logs', 'diff')) {
                $table->json('diff')->nullable();
            }
            if (! Schema::hasColumn('audit_logs', 'ip')) {
                $table->string('ip', 45)->nullable();
            }
            // user_agent may already exist as TEXT — only add if missing.
            if (! Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->string('user_agent', 500)->nullable();
            }
        });

        // Additive indexes — wrapped in try/catch via raw to avoid failure on existing index names.
        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index(['actor_type', 'actor_id', 'created_at'], 'audit_logs_actor_idx');
            });
        } catch (\Throwable $e) {
            // Index may already exist — ignore.
        }

        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index(['target_type', 'target_id'], 'audit_logs_target_idx');
            });
        } catch (\Throwable $e) {
            // Index may already exist — ignore.
        }
    }

    public function down(): void
    {
        // No destructive rollback. Keep columns and table intact.
    }
};
