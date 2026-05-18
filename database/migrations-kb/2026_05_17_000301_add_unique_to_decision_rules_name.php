<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dedupe + UNIQUE en decision_rules.name para que el seeder upsert sea idempotente.
 *
 * Bug original: DecisionRulesSeeder usaba insert() → cada kb:seed duplicaba.
 * En wellcore_kb local terminamos con 30 rules en lugar de 10.
 *
 * Solo afecta wellcore_kb (local). NO toca producción wellcore_fitness.
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        // Mantener solo el id MÁS BAJO por name (las duplicaciones son idénticas en contenido)
        DB::connection('kb')->statement(<<<'SQL'
            DELETE r1 FROM decision_rules r1
            INNER JOIN decision_rules r2
              ON r1.name = r2.name
             AND r1.id > r2.id
        SQL);

        Schema::connection('kb')->table('decision_rules', function (Blueprint $t) {
            $t->unique('name', 'decision_rules_name_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->table('decision_rules', function (Blueprint $t) {
            $t->dropUnique('decision_rules_name_unique');
        });
    }
};
