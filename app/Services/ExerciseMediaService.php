<?php

namespace App\Services;

use App\Models\EjercicioFitcron;
use App\Models\EjercicioVideo;

class ExerciseMediaService
{
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
            $name = $ex['nombre'] ?? $ex['name'] ?? '';
            $norm = $this->normalize($name);
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
     * Single query lookup: returns [normalizedName => ['gif_url' => ..., 'video_url' => ...]]
     */
    private function loadMediaByNames(array $names): array
    {
        $normalizedNames = array_map(fn ($n) => $this->normalize($n), $names);

        // Load fitcron records matching any of the normalized names
        $fitcronRows = EjercicioFitcron::query()
            ->select('slug', 'nombre', 'gif_filename', 'video_url', 'sin_fondo_listo', 'gif_path_sin_fondo')
            ->get();

        $fitcronByNorm = $fitcronRows->keyBy(fn ($row) => $this->normalize($row->nombre));

        // Collect slugs for the matched exercises to load their videos
        $slugs = collect($normalizedNames)
            ->map(fn ($norm) => $fitcronByNorm[$norm]?->slug)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $videosBySlug = EjercicioVideo::query()
            ->select('fitcron_slug', 'youtube_url')
            ->whereIn('fitcron_slug', $slugs)
            ->where('active', true)
            ->get()
            ->keyBy('fitcron_slug');

        $result = [];

        foreach ($normalizedNames as $i => $norm) {
            $fitcron = $fitcronByNorm[$norm] ?? null;

            if (! $fitcron) {
                continue;
            }

            $gifUrl = $this->resolveGifUrl($fitcron);
            $videoUrl = $videosBySlug[$fitcron->slug]?->youtube_url
                ?? $fitcron->video_url
                ?? null;

            $result[$norm] = [
                'gif_url' => $gifUrl,
                'video_url' => $videoUrl,
            ];
        }

        return $result;
    }

    private function resolveGifUrl(EjercicioFitcron $fitcron): ?string
    {
        if (! $fitcron->gif_filename) {
            return null;
        }

        // Prefer the sin_fondo version when available
        if ($fitcron->sin_fondo_listo) {
            return '/media/gif/'.$fitcron->slug;
        }

        return null;
    }

    private function normalize(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;
        $name = preg_replace('/[^a-z0-9\s]/', '', $name);

        return preg_replace('/\s+/', ' ', trim($name));
    }
}
