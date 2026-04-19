<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('plan_tickets', 'category')) {
                $table->string('category', 20)
                    ->default('plan_nuevo')
                    ->after('plan_type')
                    ->index();
            }
        });

        if (! Schema::hasTable('plan_ticket_attachments')) {
            Schema::create('plan_ticket_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('plan_ticket_id')->index();
                $table->string('uploaded_by_type', 20);
                $table->unsignedBigInteger('uploaded_by_id');
                $table->string('uploaded_by_name', 150);
                $table->string('original_name', 255);
                $table->string('stored_name', 255);
                $table->string('mime', 100);
                $table->unsignedBigInteger('size_bytes');
                $table->string('category', 50)->nullable();
                $table->string('disk', 20)->default('public');
                $table->string('path', 500);
                $table->timestamps();

                $table->foreign('plan_ticket_id')
                    ->references('id')
                    ->on('plan_tickets')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_ticket_attachments');

        Schema::table('plan_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('plan_tickets', 'category')) {
                $table->dropIndex(['category']);
                $table->dropColumn('category');
            }
        });
    }
};
