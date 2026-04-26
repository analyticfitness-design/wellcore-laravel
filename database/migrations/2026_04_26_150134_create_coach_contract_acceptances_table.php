<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('coach_contract_acceptances')) {
            return;
        }

        Schema::create('coach_contract_acceptances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('coach_id')->index();
            $table->string('contract_version', 20);
            $table->enum('status', ['accepted', 'declined']);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->char('content_hash', 64);
            $table->boolean('scroll_completed')->default(false);
            $table->timestamps();

            $table->unique(['coach_id', 'contract_version'], 'cca_coach_version_unique');
            $table->index('contract_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_contract_acceptances');
    }
};
