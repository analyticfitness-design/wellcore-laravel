<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\DataTransferObjects\Marketing\DropAsset;
use App\Models\CoachContentDrop;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Handles physical storage of drop assets and produces canonical asset entries
 * (DropAsset shape) that live inside coach_content_drops.content[assets][].
 *
 * Storage layout (disk: public):
 *   marketing/drops/{drop_id}/{asset_id}.{ext}
 *
 * Public URL served at:
 *   {APP_URL}/storage/marketing/drops/{drop_id}/{asset_id}.{ext}
 */
final class DropAssetStorage
{
    /** Allowed MIME types — kept in sync with the schema. */
    public const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'video/mp4',
    ];

    /** Maximum file size — 50 MB (kept in sync with schema). */
    public const MAX_BYTES = 52_428_800;

    /**
     * Store an uploaded file for a given drop. Returns the canonical asset entry
     * (already validated against the dropAsset schema).
     *
     * @param  array  $linkedTo  Optional linkage shape: { type, day?, reel_key?, slide_index? }
     */
    public function store(
        CoachContentDrop $drop,
        UploadedFile $file,
        ?string $role = null,
        ?array $linkedTo = null,
        ?string $caption = null,
        ?int $order = null,
        ?string $notes = null,
        ?int $uploadedById = null,
    ): DropAsset {
        $mime = $file->getMimeType() ?? 'application/octet-stream';
        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            abort(422, "MIME type no permitido: {$mime}");
        }

        $size = $file->getSize();
        if ($size === false || $size <= 0 || $size > self::MAX_BYTES) {
            abort(422, 'Tamaño de archivo invalido (max 50 MB).');
        }

        $assetId = (string) Str::ulid()->toBase32();
        $assetId = strtolower($assetId);

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'video/mp4'  => 'mp4',
        };

        $relativeDir  = "marketing/drops/{$drop->id}";
        $relativePath = "{$relativeDir}/{$assetId}.{$ext}";

        Storage::disk('public')->putFileAs(
            $relativeDir,
            $file,
            "{$assetId}.{$ext}",
            ['visibility' => 'public']
        );

        $kind = str_starts_with($mime, 'image/') ? 'image' : 'video';

        [$width, $height] = $this->dimensions($file, $kind);

        return new DropAsset(
            id:            $assetId,
            kind:          $kind,
            url:           '/storage/' . $relativePath,
            filename:      $file->getClientOriginalName() ?: "{$assetId}.{$ext}",
            mime:          $mime,
            sizeBytes:     $size,
            width:         $width,
            height:        $height,
            role:          $role,
            linkedTo:      $linkedTo,
            caption:       $caption,
            order:         $order,
            notes:         $notes,
            uploadedAt:    now()->toIso8601String(),
            uploadedById:  $uploadedById,
        );
    }

    /**
     * Delete the underlying file for an asset entry. Caller is responsible for
     * removing the entry from $drop->content['assets'].
     */
    public function deleteFile(CoachContentDrop $drop, array $asset): void
    {
        $url = $asset['url'] ?? '';
        $expectedPrefix = "/storage/marketing/drops/{$drop->id}/";
        if (! str_starts_with($url, $expectedPrefix)) {
            return; // out-of-tree URL, refuse silently
        }
        $relative = substr($url, strlen('/storage/'));
        Storage::disk('public')->delete($relative);
    }

    /**
     * Image dimensions via getimagesize. Video dimensions are not extracted
     * (returns nulls) — keep it simple, ffprobe is not bundled.
     *
     * @return array{0: ?int, 1: ?int}
     */
    private function dimensions(UploadedFile $file, string $kind): array
    {
        if ($kind !== 'image') {
            return [null, null];
        }
        $info = @getimagesize($file->getRealPath());
        if (!is_array($info)) {
            return [null, null];
        }
        return [(int) $info[0], (int) $info[1]];
    }
}
