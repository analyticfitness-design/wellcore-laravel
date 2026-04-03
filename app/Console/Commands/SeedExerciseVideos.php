<?php

namespace App\Console\Commands;

use App\Models\EjercicioFitcron;
use App\Models\EjercicioVideo;
use Illuminate\Console\Command;

class SeedExerciseVideos extends Command
{
    protected $signature = 'wellcore:seed-exercise-videos';

    protected $description = 'Seeds ejercicio_videos with known YouTube URLs, matching against ejercicios_fitcron by name.';

    private const VIDEOS = [
        'Aperturas inclinado con mancuernas' => 'https://www.youtube.com/watch?v=OU9b_aRjz0Y',
        'Aperturas inclinado en maquina' => 'https://www.youtube.com/watch?v=HDPKH1BBhhE',
        'Copa de triceps' => 'https://www.youtube.com/watch?v=CjqtisT2B2Y',
        'Cruce en polea' => 'https://www.youtube.com/watch?v=ZGUsf_jioSk',
        'Cruce inferior poleas' => 'https://www.youtube.com/watch?v=GyZKQvsGaeM',
        'Cruce invertido con polea' => 'https://www.youtube.com/watch?v=T4__GSGtnoU',
        'Cruce polea baja' => 'https://www.youtube.com/watch?v=BvuFsc_Co2E',
        'Elevaciones con mancuernas Dropset' => 'https://www.youtube.com/watch?v=uiYMBTuXhf0',
        'Elevaciones laterales con mancuerna' => 'https://www.youtube.com/watch?v=Oj3T6YBfRCE',
        'Elevaciones laterales en maquina' => 'https://www.youtube.com/watch?v=a_ltR5M_itA',
        'Elevaciones laterales en polea' => 'https://www.youtube.com/watch?v=ADOXpWcHsZ4',
        'Elevacion frontal con barra sentado' => 'https://www.youtube.com/watch?v=zlbmI4BthLk',
        'Elevacion frontal con disco' => 'https://www.youtube.com/watch?v=10e8v6Lna4k',
        'Elevacion frontal con mancuernas' => 'https://www.youtube.com/watch?v=vV5bQObGHRE',
        'Elevacion frontal con polea' => 'https://www.youtube.com/watch?v=6yoOlcgHvhA',
        'Elevacion lateral a una mano' => 'https://www.youtube.com/watch?v=6vRVVU9AzgE',
        'Elevacion lateral con banco inclinado' => 'https://www.youtube.com/watch?v=dCimwp911N0',
        'Extension de triceps a una mano' => 'https://www.youtube.com/watch?v=p3qIwP5ablo',
        'Extension de triceps con barra' => 'https://www.youtube.com/watch?v=JQa9YeIzF44',
        'Extension de triceps con soga' => 'https://www.youtube.com/watch?v=q052hSZWh0M',
        'Extension de triceps en banco inclinado' => 'https://www.youtube.com/watch?v=OAJXHoY2_2I',
        'Extension de triceps por encima de la cabeza' => 'https://www.youtube.com/watch?v=bX4zOCT_Na8',
        'Extension de triceps sentado en maquina' => 'https://www.youtube.com/watch?v=YeCM-Vl98gE',
        'Extension de triceps sobre la cabeza' => 'https://www.youtube.com/watch?v=_j18KAzEKmI',
        'Extension de triceps variacion con poleas altas' => 'https://www.youtube.com/watch?v=oTRVf2y4kEA',
        'Facepull' => 'https://www.youtube.com/watch?v=iLnhqZ_oLsQ',
        'Facepull variante jalon' => 'https://www.youtube.com/watch?v=x6cgN0bTFRo',
        'Fondos en banco' => 'https://www.youtube.com/watch?v=SswE_mcoZLA',
        'Fondos en maquina' => 'https://www.youtube.com/watch?v=rRw-yiVkE3M',
        'Jalon al pecho' => 'https://www.youtube.com/watch?v=MHhvz5IBFXk',
        'Jalon al pecho estrecho' => 'https://www.youtube.com/watch?v=sH7p91ExA0c',
        'Levantamiento de hombros' => 'https://www.youtube.com/watch?v=glX87IEgh6M',
        'Patada de triceps con mancuerna' => 'https://www.youtube.com/watch?v=qwtikDRLKN4',
        'Press arbol con mancuernas' => 'https://www.youtube.com/watch?v=zAXy_By--o0',
        'Press Banca plano con barra' => 'https://www.youtube.com/watch?v=dSKZeei9KJ4',
        'Press de agarre cerrado en banco' => 'https://www.youtube.com/watch?v=Q0n0Q4hxRLA',
        'Press en maquina' => 'https://www.youtube.com/watch?v=ojlEhV37FkU',
        'Press en smith' => 'https://www.youtube.com/watch?v=jYrcwuseZaM',
        'Press inclinado con barra' => 'https://www.youtube.com/watch?v=X2WPUgFQbWk',
        'Press inclinado con mancuernas' => 'https://www.youtube.com/watch?v=1iL_WlAYBAs',
        'Press inclinado con maquina' => 'https://www.youtube.com/watch?v=jGDYhsGlMCs',
        'Press inclinado en smith' => 'https://www.youtube.com/watch?v=LXYKjob0KMo',
        'Press militar con mancuernas' => 'https://www.youtube.com/watch?v=RhPWH-D6SRc',
        'Press plano con mancuernas' => 'https://www.youtube.com/watch?v=cma9jYjBRIw',
        'Press plano en maquina' => 'https://www.youtube.com/watch?v=D7wyM1rwbNE',
        'Press plano en smith' => 'https://www.youtube.com/watch?v=uPP6AJp1sT0',
        'Pullover' => 'https://www.youtube.com/watch?v=SANzF6jptFs',
        'Remo con polea baja a una mano' => 'https://www.youtube.com/watch?v=Gp-pRgcqWCE',
        'Remo en maquina' => 'https://www.youtube.com/watch?v=tfbBm9tWAWo',
        'Remo unilateral' => 'https://www.youtube.com/watch?v=1UL6Sb17RRI',
        'Rompecraneos con mancuerna' => 'https://www.youtube.com/watch?v=Sxlw9N3qACs',
        'Rompecreaneos con barra' => 'https://www.youtube.com/watch?v=vVf4jueIBHo',
        'Smith Close Grip Triceps' => 'https://www.youtube.com/watch?v=Rf9Bx5coELg',
        'Vuelos posteriores con mancuerna' => 'https://www.youtube.com/watch?v=ow-y0-3HSKs',
    ];

    public function handle(): int
    {
        $inserted = 0;
        $unmatched = [];

        // Load all fitcron exercises once — normalize for matching
        $fitcronByNorm = EjercicioFitcron::query()
            ->select('slug', 'nombre')
            ->get()
            ->keyBy(fn ($row) => $this->normalize($row->nombre));

        foreach (self::VIDEOS as $displayName => $youtubeUrl) {
            $norm = $this->normalize($displayName);
            $fitcron = $fitcronByNorm[$norm] ?? null;
            $fitcronSlug = $fitcron?->slug;

            if (! $fitcronSlug) {
                // Try partial LIKE search as fallback
                $fitcronSlug = $this->findSlugByLike($norm);
            }

            if (! $fitcronSlug) {
                $unmatched[] = $displayName;

                continue;
            }

            EjercicioVideo::updateOrCreate(
                ['youtube_url' => $youtubeUrl],
                [
                    'fitcron_slug' => $fitcronSlug,
                    'nombre_display' => $displayName,
                    'active' => true,
                ],
            );

            $inserted++;
        }

        $this->info("Videos insertados/actualizados (con match): {$inserted}");
        $this->info('Sin match (omitidos): '.count($unmatched));

        if ($unmatched) {
            $this->warn('Sin match ('.count($unmatched).'):');
            foreach ($unmatched as $name) {
                $this->line("  - {$name}");
            }
        }

        return self::SUCCESS;
    }

    private function normalize(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;
        $name = preg_replace('/[^a-z0-9\s]/', '', $name);

        return preg_replace('/\s+/', ' ', trim($name));
    }

    private function findSlugByLike(string $normalizedName): ?string
    {
        // Use first two significant words for a broader LIKE match
        $words = explode(' ', $normalizedName);
        $searchTerm = implode(' ', array_slice($words, 0, 3));

        $row = EjercicioFitcron::query()
            ->select('slug', 'nombre')
            ->whereRaw('LOWER(nombre) LIKE ?', ["%{$searchTerm}%"])
            ->first();

        return $row?->slug;
    }
}
