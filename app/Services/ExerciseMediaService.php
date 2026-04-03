<?php

namespace App\Services;

use App\Models\EjercicioFitcron;
use App\Models\EjercicioVideo;
use Illuminate\Support\Facades\DB;

class ExerciseMediaService
{
    private const GIF_CDN = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master';

    public function enrichWithMedia(array &$exercises): void
    {
        if (empty($exercises)) {
            return;
        }

        $names = collect($exercises)
            ->map(fn ($ex) => $ex['nombre'] ?? $ex['name'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($names)) {
            return;
        }

        $mediaByName = $this->loadMediaByNames($names);

        foreach ($exercises as &$ex) {
            $name  = $ex['nombre'] ?? $ex['name'] ?? '';
            $norm  = $this->normalize($name);
            $media = $mediaByName[$norm] ?? null;

            if (! $media) {
                continue;
            }

            if ($media['gif_url']) {
                $ex['gif_url'] = $media['gif_url'];
            }

            if ($media['video_url']) {
                $ex['video_url'] = $media['video_url'];
            }
        }
        unset($ex);
    }

    /**
     * Batch lookup: [normalizedName => ['gif_url' => ..., 'video_url' => ...]]
     *
     * Strategy:
     *  1. Exact normalized match against ejercicios_fitcron
     *  2. Fallback: exercise_name_gif_map table (pre-computed by SmartGifMatcher)
     */
    private function loadMediaByNames(array $names): array
    {
        $normalizedNames = array_map(fn ($n) => $this->normalize($n), $names);

        // ── Layer 1: exact match against fitcron ──────────────────────────────
        $fitcronRows   = EjercicioFitcron::query()
            ->select('slug', 'nombre', 'gif_filename', 'video_url')
            ->get();

        $fitcronByNorm = $fitcronRows->keyBy(fn ($row) => $this->normalize($row->nombre));

        $slugs = collect($normalizedNames)
            ->map(fn ($norm) => $fitcronByNorm[$norm]?->slug)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $videosBySlug = EjercicioVideo::query()
            ->select('fitcron_slug', 'youtube_url')
            ->whereIn('fitcron_slug', $slugs ?: ['__none__'])
            ->where('active', true)
            ->get()
            ->keyBy('fitcron_slug');

        $result   = [];
        $unmatched = []; // norms that had no exact fitcron match

        foreach ($normalizedNames as $i => $norm) {
            $fitcron = $fitcronByNorm[$norm] ?? null;

            if ($fitcron) {
                $result[$norm] = [
                    'gif_url'   => $this->gifUrl($fitcron->gif_filename),
                    'video_url' => $videosBySlug[$fitcron->slug]?->youtube_url ?? $fitcron->video_url ?? null,
                ];
            } else {
                $unmatched[$norm] = $names[$i]; // norm => original name
            }
        }

        // ── Layer 2: exercise_name_gif_map fallback ───────────────────────────
        if (! empty($unmatched)) {
            $this->enrichFromMap($unmatched, $result);
        }

        return $result;
    }

    /**
     * Look up unmatched exercise names in the pre-computed gif map table.
     * Updates $result in-place.
     */
    private function enrichFromMap(array $unmatchedNormToOriginal, array &$result): void
    {
        // Check if table exists to avoid errors in fresh environments
        try {
            $mapRows = DB::table('exercise_name_gif_map')
                ->whereNotNull('gif_filename')
                ->whereIn('nombre_plan', array_values($unmatchedNormToOriginal))
                ->select('nombre_plan', 'gif_filename', 'fitcron_slug')
                ->get()
                ->keyBy(fn ($r) => $this->normalize($r->nombre_plan));

            foreach ($unmatchedNormToOriginal as $norm => $originalName) {
                $row = $mapRows[$norm] ?? null;

                if (! $row) {
                    continue;
                }

                $videoUrl = null;
                if ($row->fitcron_slug) {
                    $video = EjercicioVideo::query()
                        ->where('fitcron_slug', $row->fitcron_slug)
                        ->where('active', true)
                        ->value('youtube_url');
                    if (! $video) {
                        $video = EjercicioFitcron::query()
                            ->where('slug', $row->fitcron_slug)
                            ->value('video_url');
                    }
                    $videoUrl = $video;
                }

                $result[$norm] = [
                    'gif_url'   => $this->gifUrl($row->gif_filename),
                    'video_url' => $videoUrl,
                ];
            }
        } catch (\Throwable) {
            // exercise_name_gif_map may not exist yet — silently skip
        }
    }

    private function gifUrl(?string $filename): ?string
    {
        return $filename ? self::GIF_CDN.'/'.rawurlencode($filename) : null;
    }

    private function normalize(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;
        $name = preg_replace('/[^a-z0-9\s]/', '', $name);

        return preg_replace('/\s+/', ' ', trim($name));
    }
}
