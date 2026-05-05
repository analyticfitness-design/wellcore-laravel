<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\CommunityPost;
use App\Models\PinnedPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PinnedPostFactory extends Factory
{
    protected $model = PinnedPost::class;

    public function definition(): array
    {
        return [
            'post_id' => CommunityPost::factory(),
            'pinned_by_type' => 'coach',
            'pinned_by_id' => Admin::factory()->state(['role' => 'coach']),
            'pinned_at' => Carbon::now(),
            'pinned_until' => Carbon::now()->addDay(),
            'note' => null,
        ];
    }
}
