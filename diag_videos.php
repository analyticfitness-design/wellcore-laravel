<?php
// Diagnostic: check ejercicio_videos and ejercicios_fitcron tables
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== TABLE CHECK ===\n";
echo "ejercicios_fitcron exists: " . (Schema::hasTable('ejercicios_fitcron') ? 'YES' : 'NO') . "\n";
echo "ejercicio_videos exists: " . (Schema::hasTable('ejercicio_videos') ? 'YES' : 'NO') . "\n";
echo "exercise_aliases exists: " . (Schema::hasTable('exercise_aliases') ? 'YES' : 'NO') . "\n\n";

if (Schema::hasTable('ejercicios_fitcron')) {
    $count = DB::table('ejercicios_fitcron')->count();
    $withVideo = DB::table('ejercicios_fitcron')->whereNotNull('video_url')->where('video_url', '!=', '')->count();
    echo "ejercicios_fitcron: {$count} rows, {$withVideo} with video_url\n";
    $sample = DB::table('ejercicios_fitcron')->whereNotNull('video_url')->limit(3)->get(['slug','nombre','video_url']);
    foreach ($sample as $r) {
        echo "  - {$r->nombre} => {$r->video_url}\n";
    }
}

echo "\n";

if (Schema::hasTable('ejercicio_videos')) {
    $count = DB::table('ejercicio_videos')->count();
    $active = DB::table('ejercicio_videos')->where('active', true)->count();
    echo "ejercicio_videos: {$count} rows, {$active} active\n";
    $sample = DB::table('ejercicio_videos')->where('active', true)->limit(3)->get(['fitcron_slug','youtube_url']);
    foreach ($sample as $r) {
        echo "  - {$r->fitcron_slug} => {$r->youtube_url}\n";
    }
} else {
    echo "ejercicio_videos: TABLE DOES NOT EXIST\n";
}

echo "\n=== ENRICHMENT TEST ===\n";
// Test with "Press Inclinado con Barra"
$testName = 'Press Inclinado con Barra';
$norm = preg_replace('/\s+/', ' ', trim(preg_replace('/[^a-z0-9\s]/', ' ', strtr(mb_strtolower(preg_replace('/\([^)]*\)/', ' ', $testName)), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n']))));
echo "Test: '{$testName}' => normalized: '{$norm}'\n";

if (Schema::hasTable('ejercicios_fitcron')) {
    $match = DB::table('ejercicios_fitcron')->whereRaw('LOWER(nombre) LIKE ?', ["%press inclinado%"])->first();
    echo "Fitcron match: " . ($match ? "{$match->slug} / {$match->nombre} / video: " . ($match->video_url ?? 'NULL') : 'NONE') . "\n";
}

if (Schema::hasTable('exercise_aliases')) {
    $alias = DB::table('exercise_aliases')->where('alias', $norm)->first();
    echo "Alias match: " . ($alias ? "gif={$alias->gif_filename} / slug={$alias->fitcron_slug}" : 'NONE') . "\n";
}

echo "\nDone.\n";
