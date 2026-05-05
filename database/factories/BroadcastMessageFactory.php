<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\BroadcastMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class BroadcastMessageFactory extends Factory
{
    protected $model = BroadcastMessage::class;

    public function definition(): array
    {
        return [
            'sender_type' => 'admin',
            'sender_id' => Admin::factory()->state(['role' => 'superadmin']),
            'audience_type' => 'clients',
            'segment_filter' => null,
            'subject' => $this->faker->sentence(3),
            'body' => $this->faker->paragraph,
            'push_enabled' => false,
            'recipients_count' => 0,
            'delivered_count' => 0,
            'sent_at' => now(),
        ];
    }
}
