<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_generation_history')) {
            return;
        }

        Schema::create('ai_generation_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->unsignedBigInteger('target_client_id')->nullable()->index();
            $table->string('plan_type', 32);
            $table->string('methodology', 80)->nullable();
            $table->unsignedSmallInteger('duration_weeks')->default(8);
            $table->json('brief_json')->nullable();
            $table->longText('output_text')->nullable();
            $table->enum('status', ['streaming', 'completed', 'aborted', 'discarded', 'approved'])
                ->default('streaming')
                ->index();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('assigned_plan_id')->nullable();
            $table->unsignedInteger('output_chars')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generation_history');
    }
};
