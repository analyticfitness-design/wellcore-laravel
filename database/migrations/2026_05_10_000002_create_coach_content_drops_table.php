<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coach_content_drops', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('coach_id');

            $table->unsignedSmallInteger('iso_year');
            $table->unsignedTinyInteger('iso_week');
            $table->date('week_starts_on');

            $table->enum('status', ['pending','generating','in_review','approved','ready','in_progress','completed','archived'])->default('pending');

            $table->json('content');
            $table->json('intake_snapshot');
            $table->string('schema_version', 20)->default('coach_drop_v1');

            $table->string('generated_by_session_id', 80)->nullable();
            $table->json('original_content')->nullable();
            $table->json('admin_edits_diff')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedInteger('reviewed_by_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('approved_by_id')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['coach_id','iso_year','iso_week'], 'uniq_coach_week');
            $table->index(['status','iso_year','iso_week'], 'idx_status_week');

            $table->foreign('coach_id')->references('id')->on('admins')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_content_drops');
    }
};
