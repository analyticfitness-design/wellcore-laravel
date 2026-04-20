<?php

namespace Database\Seeders;

use App\Models\Medal;
use Illuminate\Database\Seeder;

/**
 * Seeds the 15-medal catalog (idempotent — safe to re-run via updateOrCreate).
 *
 * Slugs, tiers, XP, stripe colors and sort order MUST match the Integration MD.
 * Sort order is spaced by 10s so new medals can be inserted without re-writing
 * the catalog.
 */
class MedalSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalog() as $data) {
            Medal::updateOrCreate(
                ['slug' => $data['slug']],
                $data + ['is_active' => true],
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function catalog(): array
    {
        return [
            // ── Constancia ──────────────────────────────────────────────────
            [
                'slug' => 'el-inicio',
                'name' => 'El Inicio',
                'description' => 'Completaste tu primer entreno WellCore.',
                'requirement' => 'Completa 1 entreno',
                'target_value' => 1,
                'xp' => 50,
                'category' => 'constancia',
                'tier' => 'bronce',
                'icon_label' => '★',
                'stripe_color_1' => '#DC2626',
                'stripe_color_2' => '#10B981',
                'stripe_color_3' => '#F59E0B',
                'sort_order' => 10,
            ],
            [
                'slug' => 'semana-fuego',
                'name' => 'Semana de Fuego',
                'description' => '7 días seguidos entrenando sin parar.',
                'requirement' => '7 días consecutivos entrenando',
                'target_value' => 7,
                'xp' => 200,
                'category' => 'constancia',
                'tier' => 'plata',
                'icon_label' => '7',
                'stripe_color_1' => '#F59E0B',
                'stripe_color_2' => '#3B82F6',
                'stripe_color_3' => '#10B981',
                'sort_order' => 20,
            ],
            [
                'slug' => 'mes-fuego',
                'name' => 'Mes de Fuego',
                'description' => '30 días consecutivos entrenando sin fallar.',
                'requirement' => '30 días consecutivos entrenando',
                'target_value' => 30,
                'xp' => 500,
                'category' => 'constancia',
                'tier' => 'oro',
                'icon_label' => '30',
                'stripe_color_1' => '#F59E0B',
                'stripe_color_2' => '#DC2626',
                'stripe_color_3' => '#10B981',
                'sort_order' => 30,
            ],

            // ── Volumen ─────────────────────────────────────────────────────
            [
                'slug' => 'entreno-10',
                'name' => 'Entreno N° 10',
                'description' => 'Diez sesiones. El hábito ya se forma.',
                'requirement' => 'Completa 10 entrenos',
                'target_value' => 10,
                'xp' => 200,
                'category' => 'volumen',
                'tier' => 'bronce',
                'icon_label' => '10',
                'stripe_color_1' => '#10B981',
                'stripe_color_2' => '#F59E0B',
                'stripe_color_3' => '#DC2626',
                'sort_order' => 40,
            ],
            [
                'slug' => 'entreno-50',
                'name' => 'Entreno N° 50',
                'description' => 'Cincuenta sesiones. Eso no se improvisa.',
                'requirement' => 'Completa 50 entrenos',
                'target_value' => 50,
                'xp' => 600,
                'category' => 'volumen',
                'tier' => 'oro',
                'icon_label' => '50',
                'stripe_color_1' => '#F59E0B',
                'stripe_color_2' => '#A78BFA',
                'stripe_color_3' => '#DC2626',
                'sort_order' => 50,
            ],
            [
                'slug' => 'entreno-200',
                'name' => 'Entreno N° 200',
                'description' => 'Doscientos entrenos. Nivel élite absoluto.',
                'requirement' => 'Completa 200 entrenos',
                'target_value' => 200,
                'xp' => 2000,
                'category' => 'volumen',
                'tier' => 'legendario',
                'icon_label' => '200',
                'stripe_color_1' => '#DC2626',
                'stripe_color_2' => '#F59E0B',
                'stripe_color_3' => '#A78BFA',
                'sort_order' => 60,
            ],

            // ── Fuerza ──────────────────────────────────────────────────────
            [
                'slug' => 'primer-pr',
                'name' => 'Primer PR',
                'description' => 'Establece tu primer récord personal.',
                'requirement' => 'Registra 1 PR',
                'target_value' => 1,
                'xp' => 150,
                'category' => 'fuerza',
                'tier' => 'bronce',
                'icon_label' => 'PR',
                'stripe_color_1' => '#DC2626',
                'stripe_color_2' => '#DC2626',
                'stripe_color_3' => '#7F1D1D',
                'sort_order' => 70,
            ],
            [
                'slug' => '10-records',
                'name' => '10 Récords',
                'description' => '10 PRs registrados. El progreso no miente.',
                'requirement' => 'Registra 10 PRs',
                'target_value' => 10,
                'xp' => 500,
                'category' => 'fuerza',
                'tier' => 'oro',
                'icon_label' => '10★',
                'stripe_color_1' => '#F59E0B',
                'stripe_color_2' => '#DC2626',
                'stripe_color_3' => '#F59E0B',
                'sort_order' => 80,
            ],
            [
                'slug' => 'monstruo-fuerza',
                'name' => 'Monstruo de Fuerza',
                'description' => '25 récords personales. Nadie te detiene.',
                'requirement' => 'Registra 25 PRs',
                'target_value' => 25,
                'xp' => 1200,
                'category' => 'fuerza',
                'tier' => 'platino',
                'icon_label' => '25★',
                'stripe_color_1' => '#A78BFA',
                'stripe_color_2' => '#DC2626',
                'stripe_color_3' => '#F59E0B',
                'sort_order' => 90,
            ],

            // ── Nutrición ───────────────────────────────────────────────────
            [
                'slug' => 'semana-macros',
                'name' => 'Semana de Macros',
                'description' => '7 días cumpliendo tus macros al 100%.',
                'requirement' => '7 días siguiendo el plan nutricional',
                'target_value' => 7,
                'xp' => 300,
                'category' => 'nutricion',
                'tier' => 'plata',
                'icon_label' => '7D',
                'stripe_color_1' => '#10B981',
                'stripe_color_2' => '#10B981',
                'stripe_color_3' => '#065F46',
                'sort_order' => 100,
            ],
            [
                'slug' => 'mes-limpio',
                'name' => 'Mes Limpio',
                'description' => '30 días siguiendo el plan nutricional.',
                'requirement' => '30 días siguiendo el plan nutricional',
                'target_value' => 30,
                'xp' => 750,
                'category' => 'nutricion',
                'tier' => 'oro',
                'icon_label' => '30D',
                'stripe_color_1' => '#10B981',
                'stripe_color_2' => '#3B82F6',
                'stripe_color_3' => '#10B981',
                'sort_order' => 110,
            ],
            [
                'slug' => 'nutricion-elite',
                'name' => 'Nutrición Elite',
                'description' => '90 días de adherencia nutricional perfecta.',
                'requirement' => '90 días siguiendo el plan nutricional',
                'target_value' => 90,
                'xp' => 1500,
                'category' => 'nutricion',
                'tier' => 'platino',
                'icon_label' => '90D',
                'stripe_color_1' => '#10B981',
                'stripe_color_2' => '#3B82F6',
                'stripe_color_3' => '#A78BFA',
                'sort_order' => 120,
            ],

            // ── Hábitos ─────────────────────────────────────────────────────
            [
                'slug' => 'habito-forjado',
                'name' => 'Hábito Forjado',
                'description' => '21 días con todos los hábitos al 100%.',
                'requirement' => '21 días hábitos completados',
                'target_value' => 21,
                'xp' => 400,
                'category' => 'habito',
                'tier' => 'plata',
                'icon_label' => '21',
                'stripe_color_1' => '#A78BFA',
                'stripe_color_2' => '#3B82F6',
                'stripe_color_3' => '#A78BFA',
                'sort_order' => 130,
            ],

            // ── Especial ────────────────────────────────────────────────────
            [
                'slug' => 'madrugador',
                'name' => 'Madrugador',
                'description' => 'Entrena antes de las 7am durante 10 días.',
                'requirement' => '10 entrenamientos antes de las 7am',
                'target_value' => 10,
                'xp' => 880,
                'category' => 'especial',
                'tier' => 'plata',
                'icon_label' => 'AM',
                'stripe_color_1' => '#F59E0B',
                'stripe_color_2' => '#DC2626',
                'stripe_color_3' => '#3B82F6',
                'sort_order' => 140,
            ],
            [
                'slug' => 'wellcore-elite',
                'name' => 'WellCore Elite',
                'description' => '1 año completo en la plataforma con resultados.',
                'requirement' => '365 días activo en WellCore',
                'target_value' => 365,
                'xp' => 5000,
                'category' => 'especial',
                'tier' => 'legendario',
                'icon_label' => 'WC',
                'stripe_color_1' => '#DC2626',
                'stripe_color_2' => '#A78BFA',
                'stripe_color_3' => '#F59E0B',
                'sort_order' => 150,
            ],
        ];
    }
}
