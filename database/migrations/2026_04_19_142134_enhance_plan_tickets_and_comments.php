<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('plan_tickets', 'deadline_at')) {
                $table->timestamp('deadline_at')->nullable()->after('rejected_at')->index();
            }

            if (! Schema::hasColumn('plan_tickets', 'parent_ticket_id')) {
                $table->unsignedBigInteger('parent_ticket_id')->nullable()->after('deadline_at')->index();
            }

            if (! Schema::hasColumn('plan_tickets', 'resubmitted_at')) {
                $table->timestamp('resubmitted_at')->nullable()->after('parent_ticket_id');
            }

            if (! Schema::hasColumn('plan_tickets', 'generated_plan_ids')) {
                $table->json('generated_plan_ids')->nullable()->after('resubmitted_at');
            }

            if (! Schema::hasColumn('plan_tickets', 'rejection_code')) {
                $table->string('rejection_code', 50)->nullable()->after('generated_plan_ids');
            }
        });

        if (! Schema::hasTable('plan_ticket_comments')) {
            Schema::create('plan_ticket_comments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('plan_ticket_id')->index();
                $table->string('author_type', 20);
                $table->unsignedBigInteger('author_id');
                $table->string('author_name', 150);
                $table->text('body');
                $table->timestamps();

                $table->index(['plan_ticket_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_ticket_comments');

        Schema::table('plan_tickets', function (Blueprint $table) {
            foreach (['deadline_at', 'parent_ticket_id', 'resubmitted_at', 'generated_plan_ids', 'rejection_code'] as $col) {
                if (Schema::hasColumn('plan_tickets', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
