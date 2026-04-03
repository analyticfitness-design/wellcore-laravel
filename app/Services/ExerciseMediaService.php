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
     * Look up unmatched exercise names in the exercise_aliases table
     * (populated by SmartGifMatcher command).
     * Updates $result in-place.
     */
    private function enrichFromMap(array $unmatchedNormToOriginal, array &$result): void
    {
        try {
            // Lookup by normalized alias (the key stored by SmartGifMatcher)
            $aliasRows = DB::table('exercise_aliases')
                ->whereNotNull('gif_filename')
                ->whereIn('alias', array_keys($unmatchedNormToOriginal))
                ->select('alias', 'gif_filename', 'fitcron_slug')
                ->get()
                ->keyBy('alias');

            foreach ($unmatchedNormToOriginal as $norm => $originalName) {
                $row = $aliasRows[$norm] ?? null;

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
            // exercise_aliases may not exist yet — silently skip
        }
    }

    private function gifUrl(?string $filename): ?string
    {
        return $filename ? self::GIF_CDN.'/'.rawurlencode($filename) : null;
    }

    private function normalize(string $name): string
    {
        // Must match SmartGifMatcher::normalizeAlias() exactly so alias keys align
        $name = preg_replace('/\([^)]*\)/', ' ', $name); // strip parentheticals
        $name = mb_strtolower(trim($name));
        $map  = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n'];
        $name = strtr($name, $map);
        $name = preg_replace('/[^a-z0-9\s]/', ' ', $name);

        return preg_replace('/\s+/', ' ', trim($name));
    }
}
