<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dedupe + UNIQUE en plan_templates_local.name para idempotencia del seeder.
 *
 * Bug original: PlanTemplatesLocalSeeder usaba insert() → cada kb:seed duplicaba.
 *
 * Solo afecta wellcore_kb (local). NO toca producción wellcore_fitness.
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        DB::connection('kb')->statement(<<<'SQL'
            DELETE r1 FROM plan_templates_local r1
            INNER JOIN plan_templates_local r2
              ON r1.name = r2.name
             AND r1.id > r2.id
        SQL);

        Schema::connection('kb')->table('plan_templates_local', function (Blueprint $t) {
            $t->unique('name', 'plan_templates_local_name_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->table('plan_templates_local', function (Blueprint $t) {
            $t->dropUnique('plan_templates_local_name_unique');
        });
    }
};
