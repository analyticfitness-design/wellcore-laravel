<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostComment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostCommentFactory extends Factory
{
    protected $model = PostComment::class;

    public function definition(): array
    {
        return [
            'post_id' => CommunityPost::factory(),
            'client_id' => Client::factory(),
            'content' => $this->faker->sentence(),
            'author_type' => 'client',
            'author_admin_id' => null,
            'created_at' => now(),
        ];
    }
}
