<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `follows` table for SP-4 Community Coach-Scoped feed.
 *
 * Tracks which clients follow each other.
 * Uses unsignedInteger for follower_id / followed_id because clients.id
 * is an increments() (unsigned INT 32-bit) column in the shared vanilla DB.
 *
 * SAFE: guarded by hasTable() — never runs twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('follows')) {
            Schema::create('follows', function (Blueprint $t) {
                $t->id();
                $t->unsignedInteger('follower_id');
                $t->unsignedInteger('followed_id');
                $t->timestamps();

                $t->unique(['follower_id', 'followed_id']);
                $t->index('followed_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
