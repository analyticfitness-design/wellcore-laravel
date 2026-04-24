<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fix FK type mismatch: page_visits.client_id was BIGINT UNSIGNED but clients.id is INT UNSIGNED.
 * MySQL rejects foreign keys between columns of different integer sizes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('page_visits')) {
            return; // table not yet created — original migration handles it correctly
        }

        // Drop FK constraint before altering column type
        try {
            DB::statement('ALTER TABLE page_visits DROP FOREIGN KEY page_visits_client_id_foreign');
        } catch (\Throwable) {
            // FK may not exist yet (e.g. fresh install where original migration had the mismatch)
        }

        // Change column from BIGINT UNSIGNED to INT UNSIGNED to match clients.id
        DB::statement('ALTER TABLE page_visits MODIFY COLUMN client_id INT UNSIGNED NULL');

        // Re-add FK constraint with correct column type
        DB::statement('ALTER TABLE page_visits ADD CONSTRAINT page_visits_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('page_visits')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE page_visits DROP FOREIGN KEY page_visits_client_id_foreign');
        } catch (\Throwable) {
        }

        DB::statement('ALTER TABLE page_visits MODIFY COLUMN client_id BIGINT UNSIGNED NULL');

        DB::statement('ALTER TABLE page_visits ADD CONSTRAINT page_visits_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL');
    }
};
