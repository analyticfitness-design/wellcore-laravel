<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\EjercicioFitcron;
use Illuminate\Http\Response;

class GifController extends Controller
{
    public function serve(string $slug): Response
    {
        // Security: only allow valid slug characters
        if (! preg_match('/^[\w\-]+$/', $slug)) {
            abort(404);
        }

        $ejercicio = EjercicioFitcron::query()
            ->select('slug', 'gif_filename', 'gif_path_sin_fondo', 'sin_fondo_listo')
            ->where('slug', $slug)
            ->first();

        if (! $ejercicio || ! $ejercicio->gif_filename) {
            abort(404);
        }

        $basePath = config('media.gif_base_path');

        $path = $this->resolvePath($ejercicio, $basePath);

        if (! $path || ! file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    private function resolvePath(EjercicioFitcron $ejercicio, string $basePath): ?string
    {
        // Prefer the sin_fondo (transparent background) version
        if ($ejercicio->sin_fondo_listo && $ejercicio->gif_path_sin_fondo) {
            $full = rtrim($basePath, '/\\').DIRECTORY_SEPARATOR.$ejercicio->gif_path_sin_fondo;
            if (file_exists($full)) {
                return $full;
            }
        }

        // Fall back to original GIF
        $full = rtrim($basePath, '/\\').DIRECTORY_SEPARATOR.$ejercicio->gif_filename;

        return file_exists($full) ? $full : null;
    }
}
