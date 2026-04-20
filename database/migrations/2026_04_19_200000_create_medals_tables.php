<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the medals catalog + client_medals pivot table.
 *
 * Strictly additive: guards every create/add with hasTable / hasColumn.
 * Safe to re-run; will NOT drop anything on rollback that pre-existed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('medals')) {
            Schema::create('medals', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('description');
                $table->string('requirement');
                $table->unsignedInteger('target_value');
                $table->unsignedInteger('xp');
                $table->enum('category', [
                    'constancia', 'volumen', 'fuerza',
                    'nutricion',  'habito',  'especial',
                ]);
                $table->enum('tier', [
                    'bronce', 'plata', 'oro', 'platino', 'legendario',
                ]);
                $table->string('icon_label', 10);
                $table->string('stripe_color_1', 7);
                $table->string('stripe_color_2', 7);
                $table->string('stripe_color_3', 7);
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('client_medals')) {
            Schema::create('client_medals', function (Blueprint $table) {
                $table->id();
                // IMPORTANT: clients.id es `int unsigned` (legacy DB schema),
                // no bigint. Usar unsignedInteger para coincidir con FK.
                $table->unsignedInteger('client_id');
                $table->unsignedBigInteger('medal_id');
                $table->unsignedInteger('current_progress')->default(0);
                $table->timestamp('achieved_at')->nullable();
                $table->timestamps();

                $table->unique(['client_id', 'medal_id']);
                $table->index(['client_id', 'achieved_at']);

                $table->foreign('client_id')
                    ->references('id')->on('clients')
                    ->cascadeOnDelete();

                $table->foreign('medal_id')
                    ->references('id')->on('medals')
                    ->cascadeOnDelete();
            });
        }

        // Aditivo: columnas de agregados en clients (solo si no existen).
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'total_xp')) {
                $table->unsignedBigInteger('total_xp')->default(0)->after('onboarding_completed');
            }
            if (! Schema::hasColumn('clients', 'total_workouts')) {
                $table->unsignedInteger('total_workouts')->default(0)->after('total_xp');
            }
            if (! Schema::hasColumn('clients', 'current_streak')) {
                $table->unsignedInteger('current_streak')->default(0)->after('total_workouts');
            }
        });
    }

    public function down(): void
    {
        // Down intencionalmente conservador: solo quita lo que EL migration creó.
        // No tocamos columnas que pudieran haberse agregado por otros migrations.
        Schema::dropIfExists('client_medals');
        Schema::dropIfExists('medals');

        Schema::table('clients', function (Blueprint $table) {
            foreach (['total_xp', 'total_workouts', 'current_streak'] as $col) {
                if (Schema::hasColumn('clients', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
