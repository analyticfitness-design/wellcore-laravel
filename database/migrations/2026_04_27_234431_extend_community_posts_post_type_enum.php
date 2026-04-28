<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aligns community_posts.post_type ENUM with the values the controller and
 * frontend already accept. Production was rejecting "pr" and "photo" with
 * SQLSTATE[01000] Data truncated, surfaced to users as
 * "Tuvimos un problema. Intenta de nuevo en unos segundos." when posting
 * a new PR from the community feed.
 *
 * Aditivo: solo agrega valores al ENUM, no toca filas existentes ni elimina
 * los valores históricos ('workout', 'milestone') que vienen de la app vanilla.
 *
 * Aplicado primero en prod via ALTER directo (2026-04-27); este archivo
 * mantiene el cambio en el historial de migraciones para nuevos entornos.
 */
return new class extends Migration
{
    private const TARGET = "ENUM('text','achievement','workout','milestone','pr','photo') DEFAULT 'text'";

    private const PREVIOUS = "ENUM('text','achievement','workout','milestone') DEFAULT 'text'";

    public function up(): void
    {
        DB::statement('ALTER TABLE community_posts MODIFY COLUMN post_type '.self::TARGET);
    }

    public function down(): void
    {
        // Solo revertir si no quedaron filas con los valores agregados.
        $stuck = DB::table('community_posts')
            ->whereIn('post_type', ['pr', 'photo'])
            ->count();

        if ($stuck > 0) {
            throw new \RuntimeException(
                "Cannot rollback: {$stuck} posts still use 'pr' or 'photo'. ".
                "Reasignar antes de revertir."
            );
        }

        DB::statement('ALTER TABLE community_posts MODIFY COLUMN post_type '.self::PREVIOUS);
    }
};
