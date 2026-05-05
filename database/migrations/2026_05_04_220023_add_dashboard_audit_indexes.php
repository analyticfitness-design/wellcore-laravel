<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // chat_messages: created_at filter en ChatAnalytics
        if (Schema::hasTable('chat_messages') && ! $this->indexExists('chat_messages', 'idx_chat_msg_created')) {
            Schema::table('chat_messages', fn (Blueprint $t) => $t->index('created_at', 'idx_chat_msg_created'));
        }

        // auth_tokens: UNIQUE en token + composite con last_used_at
        if (Schema::hasTable('auth_tokens') && ! $this->indexExists('auth_tokens', 'uq_auth_tokens_token')) {
            // Verificar duplicados primero — si los hay, abortar
            $dupes = DB::table('auth_tokens')->select('token')->groupBy('token')->havingRaw('COUNT(*) > 1')->count();
            if ($dupes > 0) {
                throw new RuntimeException("auth_tokens tiene {$dupes} tokens duplicados — no se puede agregar UNIQUE");
            }
            Schema::table('auth_tokens', fn (Blueprint $t) => $t->unique('token', 'uq_auth_tokens_token'));
        }
        if (Schema::hasTable('auth_tokens') && ! $this->indexExists('auth_tokens', 'idx_at_user_used')) {
            Schema::table('auth_tokens', fn (Blueprint $t) => $t->index(['user_type', 'user_id', 'last_used_at'], 'idx_at_user_used'));
        }

        // notifications: filter por user_type+user_id+read_at
        if (Schema::hasTable('notifications') && ! $this->indexExists('notifications', 'idx_notif_user')) {
            Schema::table('notifications', fn (Blueprint $t) => $t->index(['user_type', 'user_id', 'read_at'], 'idx_notif_user'));
        }

        // assigned_plans: scope coach + active
        if (Schema::hasTable('assigned_plans') && ! $this->indexExists('assigned_plans', 'idx_ap_assignedby_active')) {
            Schema::table('assigned_plans', fn (Blueprint $t) => $t->index(['assigned_by', 'active'], 'idx_ap_assignedby_active'));
        }
        if (Schema::hasTable('assigned_plans') && ! $this->indexExists('assigned_plans', 'idx_ap_client_active_validfrom')) {
            Schema::table('assigned_plans', fn (Blueprint $t) => $t->index(['client_id', 'active', 'valid_from'], 'idx_ap_client_active_validfrom'));
        }

        // coach_messages
        if (Schema::hasTable('coach_messages') && ! $this->indexExists('coach_messages', 'idx_cm_coach_dir_read')) {
            Schema::table('coach_messages', fn (Blueprint $t) => $t->index(['coach_id', 'direction', 'read_at'], 'idx_cm_coach_dir_read'));
        }
        if (Schema::hasTable('coach_messages') && ! $this->indexExists('coach_messages', 'idx_cm_client_created')) {
            Schema::table('coach_messages', fn (Blueprint $t) => $t->index(['client_id', 'created_at'], 'idx_cm_client_created'));
        }

        // training_logs
        if (Schema::hasTable('training_logs') && ! $this->indexExists('training_logs', 'idx_tl_client_year_week_done')) {
            Schema::table('training_logs', fn (Blueprint $t) => $t->index(['client_id', 'year_num', 'week_num', 'completed'], 'idx_tl_client_year_week_done'));
        }

        // biometric_logs
        if (Schema::hasTable('biometric_logs') && ! $this->indexExists('biometric_logs', 'idx_bl_client_logdate')) {
            Schema::table('biometric_logs', fn (Blueprint $t) => $t->index(['client_id', 'log_date'], 'idx_bl_client_logdate'));
        }

        // payments
        if (Schema::hasTable('payments') && ! $this->indexExists('payments', 'idx_p_status_created')) {
            Schema::table('payments', fn (Blueprint $t) => $t->index(['status', 'created_at'], 'idx_p_status_created'));
        }

        // clients FULLTEXT
        if (Schema::hasTable('clients') && ! $this->indexExists('clients', 'ft_clients_search')) {
            DB::statement('ALTER TABLE clients ADD FULLTEXT INDEX ft_clients_search (name, email, client_code)');
        }

        // client_coach unique
        if (Schema::hasTable('client_coach') && ! $this->indexExists('client_coach', 'uq_client_coach_unique')) {
            $dupes = DB::table('client_coach')->select('client_id', 'admin_id')->groupBy('client_id', 'admin_id')->havingRaw('COUNT(*) > 1')->count();
            if ($dupes > 0) {
                throw new RuntimeException("client_coach tiene {$dupes} pares duplicados — limpia antes de agregar UNIQUE");
            }
            Schema::table('client_coach', fn (Blueprint $t) => $t->unique(['client_id', 'admin_id'], 'uq_client_coach_unique'));
        }
    }

    public function down(): void
    {
        // Down hace drop solo si existe (idempotente)
        foreach ([
            ['chat_messages', 'idx_chat_msg_created'],
            ['auth_tokens', 'uq_auth_tokens_token'],
            ['auth_tokens', 'idx_at_user_used'],
            ['notifications', 'idx_notif_user'],
            ['assigned_plans', 'idx_ap_assignedby_active'],
            ['assigned_plans', 'idx_ap_client_active_validfrom'],
            ['coach_messages', 'idx_cm_coach_dir_read'],
            ['coach_messages', 'idx_cm_client_created'],
            ['training_logs', 'idx_tl_client_year_week_done'],
            ['biometric_logs', 'idx_bl_client_logdate'],
            ['payments', 'idx_p_status_created'],
            ['clients', 'ft_clients_search'],
            ['client_coach', 'uq_client_coach_unique'],
        ] as [$table, $index]) {
            if (Schema::hasTable($table) && $this->indexExists($table, $index)) {
                Schema::table($table, fn (Blueprint $t) => $t->dropIndex($index));
            }
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $database = DB::connection()->getDatabaseName();

        return DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $index]
        )->c > 0;
    }
};
