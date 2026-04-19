<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (! Schema::hasColumn('admins', 'email')) {
                $table->string('email', 150)->nullable()->index()->after('username');
            }
            if (! Schema::hasColumn('admins', 'whatsapp')) {
                $table->string('whatsapp', 30)->nullable()->after('email');
            }
            if (! Schema::hasColumn('admins', 'must_change_password')) {
                $table->boolean('must_change_password')->default(true)->after('whatsapp');
            }
            if (! Schema::hasColumn('admins', 'active')) {
                $table->boolean('active')->default(true)->after('must_change_password');
            }
            if (! Schema::hasColumn('admins', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            foreach (['email', 'whatsapp', 'must_change_password', 'active', 'last_login_at'] as $col) {
                if (Schema::hasColumn('admins', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
