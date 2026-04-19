<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('plan_tickets', 'plan_suplementacion')) {
                $table->json('plan_suplementacion')->nullable()->after('plan_habitos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plan_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('plan_tickets', 'plan_suplementacion')) {
                $table->dropColumn('plan_suplementacion');
            }
        });
    }
};
