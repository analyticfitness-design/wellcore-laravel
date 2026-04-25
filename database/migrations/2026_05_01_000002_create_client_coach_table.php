<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_coach', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('client_id');   // clients.id is INT UNSIGNED
            $table->unsignedInteger('admin_id');    // admins.id is INT UNSIGNED
            $table->timestamp('assigned_at')->useCurrent();
            $table->string('source', 30)->default('manual');
            $table->unsignedBigInteger('coach_invitation_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnDelete();
            $table->foreign('coach_invitation_id')
                  ->references('id')->on('coach_invitations')->nullOnDelete();

            $table->index(['client_id', 'active'], 'cc_client_active_idx');
            $table->index('admin_id', 'cc_admin_idx');
            $table->index('source', 'cc_source_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_coach');
    }
};
