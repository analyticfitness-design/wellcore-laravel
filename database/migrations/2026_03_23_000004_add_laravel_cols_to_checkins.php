<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The vanilla PHP checkins table may have a different schema from what
 * the Laravel CheckinForm component expects.
 *
 * This migration:
 *   1. Adds the Laravel-specific columns if they don't already exist.
 *   2. Makes any remaining NOT NULL vanilla PHP columns nullable so that
 *      Eloquent can INSERT without having to provide those legacy fields.
 *
 * All operations are idempotent and non-destructive.
 */
return new class extends Migration
{
    /** Columns that we always provide on INSERT — never nullify these. */
    private const ALWAYS_PROVIDED = ['id', 'client_id'];

    public function up(): void
    {
        if (! Schema::hasTable('checkins')) {
            return;
        }

        // Step 1 — add Laravel-specific columns if missing.
        Schema::table('checkins', function (Blueprint $table) {
            if (! Schema::hasColumn('checkins', 'week_label')) {
                $table->string('week_label', 20)->nullable();
            }
            if (! Schema::hasColumn('checkins', 'checkin_date')) {
                $table->date('checkin_date')->nullable();
            }
            if (! Schema::hasColumn('checkins', 'bienestar')) {
                $table->tinyInteger('bienestar')->nullable();
            }
            if (! Schema::hasColumn('checkins', 'dias_entrenados')) {
                $table->tinyInteger('dias_entrenados')->nullable()->default(0);
            }
            if (! Schema::hasColumn('checkins', 'nutricion')) {
                $table->string('nutricion', 20)->nullable();
            }
            if (! Schema::hasColumn('checkins', 'comentario')) {
                $table->text('comentario')->nullable();
            }
            if (! Schema::hasColumn('checkins', 'rpe')) {
                $table->tinyInteger('rpe')->nullable();
            }
            if (! Schema::hasColumn('checkins', 'coach_reply')) {
                $table->text('coach_reply')->nullable();
            }
            if (! Schema::hasColumn('checkins', 'replied_at')) {
                $table->timestamp('replied_at')->nullable();
            }
        });

        // Step 2 — make all remaining NOT NULL vanilla PHP columns nullable.
        // We read the live column definition before issuing ALTER TABLE so we
        // preserve the original type, length, and unsigned flag exactly.
        $columns = DB::select('SHOW COLUMNS FROM checkins');

        foreach ($columns as $col) {
            // Never touch primary key or columns we always provide.
            if (in_array($col->Field, self::ALWAYS_PROVIDED, true)) {
                continue;
            }
            // Already nullable — nothing to do.
            if ($col->Null === 'YES') {
                continue;
            }

            $defaultClause = $this->buildDefaultClause($col->Default);

            DB::statement(
                "ALTER TABLE checkins MODIFY COLUMN `{$col->Field}` {$col->Type} NULL {$defaultClause}"
            );
        }
    }

    public function down(): void
    {
        // Intentional no-op: reverting NULL constraints would risk data loss.
    }

    private function buildDefaultClause(?string $default): string
    {
        if ($default === null) {
            return 'DEFAULT NULL';
        }

        // MySQL function defaults (CURRENT_TIMESTAMP, NOW()) must not be quoted.
        if (preg_match('/^(CURRENT_TIMESTAMP|NOW\(\)|CURRENT_DATE)/i', $default)) {
            return "DEFAULT {$default}";
        }

        return is_numeric($default)
            ? "DEFAULT {$default}"
            : "DEFAULT '{$default}'";
    }
};
