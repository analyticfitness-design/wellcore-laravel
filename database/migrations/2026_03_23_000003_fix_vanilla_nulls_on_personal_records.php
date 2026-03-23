<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The vanilla PHP personal_records table has columns that are NOT NULL:
 *   exercise_id (FK), value, rpe, recorded_at
 *
 * Laravel's PersonalRecord model does NOT provide these on INSERT (they are
 * vanilla-only fields irrelevant to the new schema).  This migration makes
 * them nullable so that Eloquent can insert rows without providing them.
 *
 * It also adds created_at / updated_at (for Eloquent's automatic timestamps)
 * and makes any existing NOT NULL timestamp columns nullable so Eloquent
 * can manage them cleanly.
 *
 * All operations are idempotent and non-destructive.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personal_records')) {
            return;
        }

        // Vanilla PHP columns that Laravel does not provide on INSERT.
        // If any are NOT NULL without a default, the INSERT would fail.
        $this->makeNullableIfNeeded(['exercise_id', 'value', 'rpe', 'recorded_at']);

        // Add Eloquent timestamp columns if they don't already exist.
        Schema::table('personal_records', function (Blueprint $table) {
            if (! Schema::hasColumn('personal_records', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('personal_records', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        // If created_at / updated_at already exist but are NOT NULL, make them nullable.
        $this->makeNullableIfNeeded(['created_at', 'updated_at']);
    }

    public function down(): void
    {
        // Intentional no-op: reverting NULL constraints would risk data integrity.
    }

    /**
     * For each column in $columns: if it exists and is NOT NULL,
     * re-issue an ALTER TABLE … MODIFY to drop the NOT NULL constraint.
     * We read the live column definition first so we preserve type, length,
     * unsigned flag, character set, and any existing DEFAULT.
     */
    private function makeNullableIfNeeded(array $columns): void
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn('personal_records', $column)) {
                continue;
            }

            $info = DB::selectOne(
                'SHOW COLUMNS FROM personal_records WHERE Field = ?',
                [$column]
            );

            if (! $info || $info->Null === 'YES') {
                continue; // already nullable, nothing to do
            }

            // Build a safe DEFAULT clause.
            $defaultClause = 'DEFAULT NULL';
            if ($info->Default !== null) {
                $default = $info->Default;
                // Numeric defaults need no quotes; everything else gets quoted.
                $defaultClause = is_numeric($default)
                    ? "DEFAULT {$default}"
                    : "DEFAULT '{$default}'";
            }

            DB::statement(
                "ALTER TABLE personal_records MODIFY COLUMN `{$column}` {$info->Type} NULL {$defaultClause}"
            );
        }
    }
};
