<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aditiva (cumple ADR-0003): agrega infraestructura i18n para soportar en-US
 * en dashboards de cliente y coach sin tocar app vanilla PHP.
 *
 * Schema decidido en docs/I18N_AUDIT_2026-05-17.md (decisiones Daniel §0).
 *
 * - VARCHAR(10) en vez de ENUM: lección de clients.plan ENUM-en-prod (2026-04).
 *   Permite agregar pt-BR, es-MX, en-GB en el futuro sin migrar tipo.
 * - Valores 'es'/'en' (no 'es-CO'/'en-US') para alinear con SetLocale middleware
 *   existente y archivos lang/es/ + lang/en/.
 * - admins.currency: dashboard coach muestra USD cuando coach es en-US.
 * - assigned_plans.plan_data_en: motor v2 traduce branches user-facing al PERSIST.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table): void {
                if (! Schema::hasColumn('clients', 'locale')) {
                    $table->string('locale', 10)->notNullable()->default('es')->after('timezone');
                }
                if (! Schema::hasColumn('clients', 'locale_locked')) {
                    $table->boolean('locale_locked')->notNullable()->default(false)->after('locale');
                }
                if (! Schema::hasColumn('clients', 'unit_system')) {
                    $table->string('unit_system', 10)->notNullable()->default('metric')->after('locale_locked');
                }
            });
        }

        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table): void {
                if (! Schema::hasColumn('admins', 'locale')) {
                    $table->string('locale', 10)->notNullable()->default('es')->after('email');
                }
                if (! Schema::hasColumn('admins', 'locale_locked')) {
                    $table->boolean('locale_locked')->notNullable()->default(false)->after('locale');
                }
                if (! Schema::hasColumn('admins', 'currency')) {
                    $table->string('currency', 3)->notNullable()->default('COP')->after('locale_locked');
                }
            });
        }

        if (Schema::hasTable('assigned_plans') && ! Schema::hasColumn('assigned_plans', 'plan_data_en')) {
            // Producción tiene la columna user-facing como `content` (longtext);
            // ambientes locales pueden tener `plan_data`. Anclamos al primer candidato
            // existente; si ninguno existe, agregamos al final sin after().
            Schema::table('assigned_plans', function (Blueprint $table): void {
                $column = $table->json('plan_data_en')->nullable();
                if (Schema::hasColumn('assigned_plans', 'plan_data')) {
                    $column->after('plan_data');
                } elseif (Schema::hasColumn('assigned_plans', 'content')) {
                    $column->after('content');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table): void {
                foreach (['unit_system', 'locale_locked', 'locale'] as $col) {
                    if (Schema::hasColumn('clients', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table): void {
                foreach (['currency', 'locale_locked', 'locale'] as $col) {
                    if (Schema::hasColumn('admins', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('assigned_plans') && Schema::hasColumn('assigned_plans', 'plan_data_en')) {
            Schema::table('assigned_plans', function (Blueprint $table): void {
                $table->dropColumn('plan_data_en');
            });
        }
    }
};
