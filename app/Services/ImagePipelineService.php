<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use InvalidArgumentException;

/**
 * Processes image uploads: validates, fixes EXIF, downscales, emits WebP + fallback.
 */
class ImagePipelineService
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    private const MAX_INPUT_BYTES = 10 * 1024 * 1024;

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
        $realPath = $file->getRealPath();

        if (! $realPath || ! is_file($realPath)) {
            throw new InvalidArgumentException('Archivo inválido o no legible.');
        }

        if ($file->getSize() > self::MAX_INPUT_BYTES) {
            throw new InvalidArgumentException('Imagen excede 10 MB.');
        }

        $mime = @finfo_file(finfo_open(FILEINFO_MIME_TYPE), $realPath) ?: null;

        if (! $mime || ! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new InvalidArgumentException("MIME no permitido: {$mime}");
        }

        $manager = new ImageManager(new GdDriver);
        $image = $manager->read($realPath);

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
    }

    /**
     * Remove WebP + fallback artifacts from storage.
     */
    public function delete(string $pathWebp, string $pathFallback, string $disk = 'public'): void
    {
        Storage::disk($disk)->delete([$pathWebp, $pathFallback]);
    }
}
