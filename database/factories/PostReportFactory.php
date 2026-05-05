<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostReportFactory extends Factory
{
    protected $model = PostReport::class;

    public function definition(): array
    {
        return [
            'post_id' => CommunityPost::factory(),
            'reporter_id' => Client::factory(),
            'reason' => $this->faker->randomElement(['spam', 'offensive', 'off_topic', 'other']),
            'status' => 'pending',
            'created_at' => now(),
        ];
    }
}
