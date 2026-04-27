<?php
declare(strict_types=1);
namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachMarketingProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CoachMarketingProfileFactory extends Factory
{
    protected $model = CoachMarketingProfile::class;

    public function definition(): array
    {
        return [
            'coach_id'                  => Admin::factory()->state(['role' => UserRole::Coach->value]),
            'brand_name'                => fake()->name(),
            'city'                      => fake()->city(),
            'country_code'              => 'CO',
            'specialty_primary'         => 'fuerza',
            'differentiator'            => fake()->sentence(8),
            'audience_age_range'        => '25-35',
            'audience_gender'           => 'mixto',
            'audience_pain_main'        => fake()->sentence(6),
            'audience_offer_main'       => 'metodo',
            'preferred_methodologies'   => ['sobrecarga_progresiva','deficit_calorico'],
            'content_topics'            => ['mitos_fitness','transformaciones'],
            'voice_adjectives'          => ['directo','tecnico','cercano'],
            'active_offers'             => [['name'=>'Método','price'=>120,'currency'=>'USD','promo'=>null]],
            'last_updated_by'           => 'coach',
            'completed_at'              => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(['completed_at' => now()]);
    }
}
