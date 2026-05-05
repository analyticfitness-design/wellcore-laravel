<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\CommunityPost;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunityPostFactory extends Factory
{
    protected $model = CommunityPost::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'coach_admin_id' => null,
            'content' => $this->faker->paragraph(),
            'post_type' => 'text',
            'image_path' => null,
            'visible' => true,
            'author_type' => 'client',
            'author_admin_id' => null,
            'is_official' => false,
            'is_global' => false,
        ];
    }
}
