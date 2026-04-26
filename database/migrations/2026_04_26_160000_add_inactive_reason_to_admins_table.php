<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admins')) {
            return;
        }
        if (Schema::hasColumn('admins', 'inactive_reason')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            $table->string('inactive_reason', 50)->nullable()->after('active');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('admins')) {
            return;
        }
        if (! Schema::hasColumn('admins', 'inactive_reason')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('inactive_reason');
        });
    }
};
