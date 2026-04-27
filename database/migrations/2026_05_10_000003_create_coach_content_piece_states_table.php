<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coach_content_piece_states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('drop_id');
            $table->unsignedInteger('coach_id');

            $table->enum('piece_type', ['reel','story','checklist_phase']);
            $table->string('piece_key', 40);

            $table->enum('state', ['pending','in_progress','published','skipped'])->default('pending');
            $table->string('published_url', 500)->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('state_changed_at')->nullable();
            $table->timestamps();

            $table->unique(['drop_id','piece_type','piece_key'], 'uniq_piece');
            $table->foreign('drop_id')->references('id')->on('coach_content_drops')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_content_piece_states');
    }
};
