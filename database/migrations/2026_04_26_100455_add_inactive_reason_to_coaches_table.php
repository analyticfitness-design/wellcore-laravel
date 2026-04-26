<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('coaches')) {
            return; // shared schema may not have it yet — defensive
        }
        if (Schema::hasColumn('coaches', 'inactive_reason')) {
            return; // already added by vanilla app or earlier migration
        }

        Schema::table('coaches', function (Blueprint $table) {
            $table->string('inactive_reason', 50)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('coaches')) {
            return;
        }
        if (! Schema::hasColumn('coaches', 'inactive_reason')) {
            return;
        }

        Schema::table('coaches', function (Blueprint $table) {
            $table->dropColumn('inactive_reason');
        });
    }
};
