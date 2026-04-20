<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Coach onboarding checklist state persistente (no solo localStorage).
// Aditiva e idempotente.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (! Schema::hasColumn('admins', 'onboarding_state')) {
                $table->json('onboarding_state')->nullable()->after('must_change_password');
            }
        });
    }

    public function down(): void
    {
        // no-op defensivo
    }
};
