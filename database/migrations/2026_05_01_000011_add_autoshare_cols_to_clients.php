<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds autoshare preference columns to `clients` for SP-4 Community feed.
 *
 * Each flag controls whether a client's activity is automatically shared
 * as a community post when the event occurs.
 *
 * Defaults reflect sensible privacy settings:
 *   - workout:  off  (high-frequency; opt-in)
 *   - pr:       on   (achievements are social by nature)
 *   - medal:    on   (achievements are social by nature)
 *   - weight:   off  (sensitive body data; opt-in)
 *   - streak:   on   (motivational; opt-in out)
 *
 * SAFE: guarded by hasColumn() on the first new column — never runs twice.
 * after() is MySQL-only and is intentional here (shared MySQL DB).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('clients', 'autoshare_workout')) {
            Schema::table('clients', function (Blueprint $t) {
                $t->boolean('autoshare_workout')->default(false)->after('status');
                $t->boolean('autoshare_pr')->default(true)->after('autoshare_workout');
                $t->boolean('autoshare_medal')->default(true)->after('autoshare_pr');
                $t->boolean('autoshare_weight')->default(false)->after('autoshare_medal');
                $t->boolean('autoshare_streak')->default(true)->after('autoshare_weight');
            });
        }
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $t) {
            foreach (['autoshare_workout', 'autoshare_pr', 'autoshare_medal', 'autoshare_weight', 'autoshare_streak'] as $col) {
                if (Schema::hasColumn('clients', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};
