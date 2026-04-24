<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * D.3 — Add missing performance indexes.
 * All operations use  (MySQL online DDL, no downtime).
 * Each index is guarded by Schema::hasIndex to be idempotent on re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        // clients.status — common filter in admin dashboard queries
        if (Schema::hasTable('clients') && ! Schema::hasIndex('clients', 'idx_clients_status')) {
            DB::statement('ALTER TABLE clients ADD INDEX idx_clients_status (status) ');
        }

        // clients.(status, created_at) — paginated list sorted by date filtered by status
        if (Schema::hasTable('clients') && ! Schema::hasIndex('clients', 'idx_clients_status_created')) {
            DB::statement('ALTER TABLE clients ADD INDEX idx_clients_status_created (status, created_at) ');
        }

        // payments.(client_id, status) — client payment history with status filter
        if (Schema::hasTable('payments') && ! Schema::hasIndex('payments', 'idx_payments_client_status')) {
            DB::statement('ALTER TABLE payments ADD INDEX idx_payments_client_status (client_id, status) ');
        }

        // payments.status — admin payment dashboard filter
        if (Schema::hasTable('payments') && ! Schema::hasIndex('payments', 'idx_payments_status')) {
            DB::statement('ALTER TABLE payments ADD INDEX idx_payments_status (status) ');
        }

        // checkins.(client_id, checkin_date) — client check-in history range queries
        if (Schema::hasTable('checkins') && ! Schema::hasIndex('checkins', 'idx_checkins_client_date')) {
            DB::statement('ALTER TABLE checkins ADD INDEX idx_checkins_client_date (client_id, checkin_date) ');
        }
    }

    public function down(): void
    {
        $drops = [
            'clients'  => ['idx_clients_status', 'idx_clients_status_created'],
            'payments' => ['idx_payments_client_status', 'idx_payments_status'],
            'checkins' => ['idx_checkins_client_date'],
        ];

        foreach ($drops as $table => $indexes) {
            foreach ($indexes as $index) {
                if (Schema::hasTable($table) && Schema::hasIndex($table, $index)) {
                    DB::statement("ALTER TABLE {$table} DROP INDEX {$index}");
                }
            }
        }
    }
};
