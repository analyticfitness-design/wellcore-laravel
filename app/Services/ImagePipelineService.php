<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Processes image uploads: validates, fixes EXIF, downscales, emits WebP + fallback.
 */
class ImagePipelineService
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    private const MAX_INPUT_BYTES = 10 * 1024 * 1024;

    /**
     * Memory headroom required to decode a raw RGBA bitmap plus copies
     * (decode + scale + WebP encode + JPEG encode). iPhone shoots 12 MP images
     * which need ~48 MB decoded, and encoders need another ~2x that while working.
     */
    private const MEMORY_FLOOR_BYTES = 512 * 1024 * 1024;

    /**
     * Process an uploaded image and persist WebP + original-format fallback.
     *
     * @return array{
     *   path_webp:string, path_fallback:string,
     *   url_webp:string, url_fallback:string,
     *   width:int, height:int,
     *   size_bytes_webp:int, size_bytes_fallback:int,
     *   mime_original:string
     * }
     */
    public function processUpload(
        UploadedFile $file,
        string $disk = 'public',
        string $directory = 'uploads',
        int $maxWidth = 2000,
        int $quality = 85,
    ): array {
        // Modern phone cameras shoot 12–48 MP; decoding + scaling + dual encoding
        // easily blows past the default 128 MB. Scoped to this method only.
        $this->ensureMemoryHeadroom();

        $realPath = $file->getRealPath();

        if (! $realPath || ! is_file($realPath)) {
            throw new InvalidArgumentException('Archivo invalido o no legible.');
        }

        if ($file->getSize() > self::MAX_INPUT_BYTES) {
            throw new InvalidArgumentException('La imagen excede 10 MB. Reducela antes de subirla.');
        }

        $mime = $this->detectMime($realPath);

        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new InvalidArgumentException(
                'Formato no soportado. Usa JPG, PNG o WebP. Si tu iPhone toma en HEIC, cambia Ajustes > Camara > Formatos a "Mas compatible".'
            );
        }

        try {
            $manager = new ImageManager(new GdDriver);
            // Intervention v4: use decode(), NOT read() (v3 API).
            $image = $manager->decode($realPath);

            // EXIF orientation fix (no-op for formats sin EXIF).
            if (method_exists($image, 'orient')) {
                $image->orient();
            }

            if ($image->width() > $maxWidth) {
                $image->scaleDown(width: $maxWidth);
            }

            $uuid = (string) Str::uuid();
            $fallbackExt = $mime === 'image/png' ? 'png' : 'jpg';
            $pathWebp = trim($directory, '/')."/{$uuid}.webp";
            $pathFallback = trim($directory, '/')."/{$uuid}.{$fallbackExt}";

            $webpBinary = (string) $image->encode(new WebpEncoder(quality: $quality));

            $fallbackBinary = (string) match ($mime) {
                'image/png' => $image->encode(new PngEncoder),
                default => $image->encode(new JpegEncoder(quality: $quality)),
            };

            Storage::disk($disk)->put($pathWebp, $webpBinary);
            Storage::disk($disk)->put($pathFallback, $fallbackBinary);

            return [
                'path_webp' => $pathWebp,
                'path_fallback' => $pathFallback,
                'url_webp' => Storage::disk($disk)->url($pathWebp),
                'url_fallback' => Storage::disk($disk)->url($pathFallback),
                'width' => $image->width(),
                'height' => $image->height(),
                'size_bytes_webp' => strlen($webpBinary),
                'size_bytes_fallback' => strlen($fallbackBinary),
                'mime_original' => $mime,
            ];
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('ImagePipelineService processUpload failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile().':'.$e->getLine(),
                'upload_size' => $file->getSize(),
                'mime' => $mime ?? 'unknown',
                'original_name' => $file->getClientOriginalName(),
            ]);

            throw new RuntimeException('No pudimos procesar la imagen. Intenta con otra foto o en formato JPG.', previous: $e);
        }
    }

    private function detectMime(string $realPath): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return '';
        }

        $mime = finfo_file($finfo, $realPath);
        finfo_close($finfo);

        return is_string($mime) ? $mime : '';
    }

    private function ensureMemoryHeadroom(): void
    {
        $current = $this->parseIniSize((string) ini_get('memory_limit'));

        if ($current === -1 || $current >= self::MEMORY_FLOOR_BYTES) {
            return;
        }

        @ini_set('memory_limit', (string) self::MEMORY_FLOOR_BYTES);
    }

    private function parseIniSize(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    /**
     * Remove WebP + fallback artifacts from storage.
     */
    public function delete(string $pathWebp, string $pathFallback, string $disk = 'public'): void
    {
        Storage::disk($disk)->delete([$pathWebp, $pathFallback]);
    }
}
