<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('progress_photo_notes')) {
            return;
        }

        Schema::create('progress_photo_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('photo_id');
            $table->unsignedBigInteger('coach_id');
            $table->unsignedBigInteger('client_id');
            $table->text('note_text');
            $table->float('x_pct')->nullable();
            $table->float('y_pct')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('read_at')->nullable();

            $table->index(['photo_id', 'coach_id'], 'idx_photo_coach');
            $table->index(['client_id', 'read_at'], 'idx_client_unread');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_photo_notes');
    }
};
