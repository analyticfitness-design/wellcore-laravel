<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega evidence_level a wellcore_kb.principles.
 *
 * Valores siguen el estándar de catálogos hermanos (supplement_catalog):
 *   - muy_alta: meta-análisis sólidos (Schoenfeld, Helms, ISSN position statements)
 *   - alta: estudios randomizados controlados con muestra grande
 *   - moderada: estudios pequeños o consenso experto sin meta-análisis
 *   - limitada: 1-2 estudios o evidencia anecdótica fuerte
 *   - anecdotica: experiencia clínica sin RCT
 *
 * Default 'alta' para entries existentes (los principles seedeados ya
 * mencionan referencias en sus descriptions).
 *
 * Usado por PrincipleInjector (Sprint 58) como tiebreak: cuando 2+ principles
 * tienen mismo score, prevalece el de evidencia más alta.
 *
 * Aditiva. Solo wellcore_kb local. NO toca producción.
 */
return new class extends Migration
{
    protected $connection = 'kb';

    public function up(): void
    {
        Schema::connection('kb')->table('principles', function (Blueprint $t) {
            $t->enum('evidence_level', ['muy_alta', 'alta', 'moderada', 'limitada', 'anecdotica'])
                ->default('alta')
                ->after('tags');
            $t->index(['vertical', 'evidence_level']);
        });
    }

    public function down(): void
    {
        Schema::connection('kb')->table('principles', function (Blueprint $t) {
            $t->dropIndex(['vertical', 'evidence_level']);
            $t->dropColumn('evidence_level');
        });
    }
};
