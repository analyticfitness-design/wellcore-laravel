<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin', ['title' => 'Configuracion'])]
class AdminSettings extends Component
{
    public array $config = [];
    public array $features = [];

    public function mount(): void
    {
        $this->config = [
            'app_name' => config('app.name', 'WellCore'),
            'app_url' => config('app.url', ''),
            'app_env' => config('app.env', 'production'),
            'mail_mailer' => config('mail.default', 'smtp'),
            'mail_from_address' => config('mail.from.address', ''),
            'mail_from_name' => config('mail.from.name', ''),
            'db_connection' => config('database.default', 'mysql'),
            'db_database' => config('database.connections.mysql.database', ''),
            'db_host' => config('database.connections.mysql.host', ''),
            'cache_store' => config('cache.default', 'file'),
            'session_driver' => config('session.driver', 'file'),
            'queue_connection' => config('queue.default', 'sync'),
        ];

        $this->features = [
            ['name' => 'AI Nutrition', 'key' => 'ai-nutrition', 'enabled' => true],
            ['name' => 'AI Plan Generator', 'key' => 'ai-generator', 'enabled' => true],
            ['name' => 'Community Feed', 'key' => 'community', 'enabled' => true],
            ['name' => 'Video Check-ins', 'key' => 'video-checkin', 'enabled' => true],
            ['name' => 'Chat Widget', 'key' => 'chat', 'enabled' => true],
            ['name' => 'RISE Program', 'key' => 'rise', 'enabled' => true],
            ['name' => 'Shop / Tienda', 'key' => 'shop', 'enabled' => true],
            ['name' => 'Referral Program', 'key' => 'referrals', 'enabled' => true],
            ['name' => 'Coach Portal', 'key' => 'coach', 'enabled' => true],
            ['name' => 'Presencial', 'key' => 'presencial', 'enabled' => true],
        ];
    }

    public function render()
    {
        return view('livewire.admin.admin-settings');
    }
}
