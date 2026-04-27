<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Models\CoachContentDrop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

final class StrategyAssetController extends Controller
{
    /**
     * Stream a single asset by id (authenticated). Even though the underlying
     * file is publicly addressable via /storage/..., this route lets us add
     * Content-Disposition: attachment so the browser actually downloads it
     * with the original filename.
     */
    public function show(Request $request, CoachContentDrop $drop, string $assetId): StreamedResponse
    {
        Gate::authorize('view', $drop);

        $asset = $this->findAsset($drop, $assetId);
        $relative = $this->relativePath($drop, $asset);

        if (! Storage::disk('public')->exists($relative)) {
            abort(404, 'Asset file no encontrado en disco.');
        }

        return Storage::disk('public')->download($relative, $asset['filename']);
    }

    /**
     * Build a ZIP on-the-fly with all assets of the drop. Filenames inside the
     * ZIP follow the pattern {order}-{role}-{filename} so the coach receives
     * the materials in a sensible order when extracted.
     */
    public function zip(Request $request, CoachContentDrop $drop): StreamedResponse
    {
        Gate::authorize('view', $drop);

        $assets = $drop->content['assets'] ?? [];
        if (count($assets) === 0) {
            abort(404, 'Este drop no tiene assets.');
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'wc-drop-zip-');
        $zip = new ZipArchive();
        if ($zip->open($tempPath, ZipArchive::OVERWRITE) !== true) {
            abort(500, 'No se pudo crear el ZIP.');
        }

        // Deterministic order: by order asc, then by role asc, then by index.
        usort($assets, function (array $a, array $b): int {
            $oa = $a['order'] ?? 999;
            $ob = $b['order'] ?? 999;
            if ($oa !== $ob) {
                return $oa <=> $ob;
            }
            return strcmp((string)($a['role'] ?? ''), (string)($b['role'] ?? ''));
        });

        $diskRoot = Storage::disk('public')->path('');
        foreach ($assets as $i => $asset) {
            $relative = $this->relativePath($drop, $asset);
            $absolute = $diskRoot . $relative;
            if (! is_file($absolute)) {
                continue;
            }
            $entryName = sprintf(
                '%02d-%s-%s',
                $i + 1,
                preg_replace('/[^a-z0-9_-]/i', '', (string)($asset['role'] ?? 'asset')),
                $asset['filename'] ?? "asset-{$asset['id']}",
            );
            $zip->addFile($absolute, $entryName);
        }

        $zip->close();

        $coachName = (string)($drop->coach->name ?? "coach-{$drop->coach_id}");
        $coachSlug = preg_replace('/[^a-z0-9-]/i', '-', strtolower($coachName));
        $zipName   = sprintf('wellcore-%s-%d-W%02d.zip', $coachSlug, $drop->iso_year, $drop->iso_week);

        return response()->streamDownload(function () use ($tempPath) {
            readfile($tempPath);
            @unlink($tempPath);
        }, $zipName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /** @return array<string, mixed> */
    private function findAsset(CoachContentDrop $drop, string $assetId): array
    {
        foreach (($drop->content['assets'] ?? []) as $asset) {
            if (($asset['id'] ?? null) === $assetId) {
                return $asset;
            }
        }
        abort(404, 'Asset no encontrado.');
    }

    private function relativePath(CoachContentDrop $drop, array $asset): string
    {
        $url = $asset['url'] ?? '';
        $expectedPrefix = "/storage/marketing/drops/{$drop->id}/";
        if (! str_starts_with($url, $expectedPrefix)) {
            abort(404, 'URL invalida.');
        }
        return substr($url, strlen('/storage/'));
    }
}
