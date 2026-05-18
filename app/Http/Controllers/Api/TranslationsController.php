<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

/**
 * Endpoint público que sirve el bundle de traducciones de Laravel (lang/{locale}/*.php)
 * como JSON consumible por vue-i18n.
 *
 * Cache: Redis 1h + ETag basado en hash del directorio para revalidación rápida.
 * Diseño: ADR-0004 i18n en-US.
 */
class TranslationsController extends Controller
{
    private const SUPPORTED = ['es', 'en'];
    private const CACHE_TTL_SECONDS = 3600;

    public function show(Request $request, string $locale): JsonResponse|Response
    {
        if (! in_array($locale, self::SUPPORTED, true)) {
            return response()->json(['message' => 'locale not supported'], 404);
        }

        $cacheKey = "i18n.translations.{$locale}";

        $payload = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($locale) {
            return $this->buildBundle($locale);
        });

        $etag = '"' . substr($payload['hash'], 0, 16) . '"';

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'public, max-age=300, must-revalidate');
        }

        return response()
            ->json([
                'locale' => $locale,
                'messages' => $payload['messages'],
                'version' => $payload['hash'],
            ])
            ->header('ETag', $etag)
            ->header('Cache-Control', 'public, max-age=300, must-revalidate')
            ->header('Vary', 'Accept-Encoding');
    }

    /**
     * Lee lang/{locale}/*.php, los namespace-a por filename y devuelve el bundle.
     */
    private function buildBundle(string $locale): array
    {
        $dir = base_path("lang/{$locale}");

        if (! File::isDirectory($dir)) {
            return ['messages' => new \stdClass(), 'hash' => 'empty'];
        }

        $messages = [];
        $hashSeed = '';

        foreach (File::files($dir) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $namespace = $file->getFilenameWithoutExtension();
            $contents = require $file->getRealPath();

            if (is_array($contents)) {
                $messages[$namespace] = $contents;
                $hashSeed .= $namespace . ':' . md5_file($file->getRealPath());
            }
        }

        return [
            'messages' => $messages,
            'hash' => substr(hash('sha256', $hashSeed), 0, 32),
        ];
    }
}
