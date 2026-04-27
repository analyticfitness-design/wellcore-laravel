<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coach_marketing_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('coach_id')->unique();

            $table->string('brand_name', 120);
            $table->string('city', 80)->nullable();
            $table->char('country_code', 2)->nullable();

            $table->enum('specialty_primary', ['fuerza','hipertrofia','recomposicion','perdida_grasa','mujeres_postparto','funcional','otro']);
            $table->string('specialty_primary_other', 80)->nullable();
            $table->enum('specialty_secondary', ['fuerza','hipertrofia','recomposicion','perdida_grasa','mujeres_postparto','funcional','otro'])->nullable();
            $table->string('specialty_secondary_other', 80)->nullable();
            $table->text('differentiator');

            $table->enum('audience_age_range', ['18-25','25-35','35-45','45+']);
            $table->enum('audience_gender', ['mujeres','hombres','mixto']);
            $table->string('audience_pain_main', 200);
            $table->enum('audience_offer_main', ['esencial','metodo','elite','presencial','otro']);

            $table->json('preferred_methodologies');
            $table->json('preferred_methodologies_other')->nullable();
            $table->json('content_topics');
            $table->json('content_topics_other')->nullable();
            $table->json('voice_adjectives');
            $table->json('voice_samples')->nullable();
            $table->json('active_offers');
            $table->json('top_working_posts')->nullable();

            $table->timestamp('completed_at')->nullable()->index();
            $table->enum('last_updated_by', ['coach','admin']);
            $table->unsignedInteger('last_admin_editor_id')->nullable();

            $table->timestamps();

            $table->foreign('coach_id')->references('id')->on('admins')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_marketing_profiles');
    }
};
