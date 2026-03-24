<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clients') && ! Schema::hasColumn('clients', 'onboarding_completed')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->boolean('onboarding_completed')->default(false)->after('referred_by');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'onboarding_completed')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('onboarding_completed');
            });
        }
    }
};
