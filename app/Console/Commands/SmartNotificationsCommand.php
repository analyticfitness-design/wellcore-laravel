<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\SmartNotificationService;
use Illuminate\Console\Command;

class SmartNotificationsCommand extends Command
{
    protected $signature = 'wellcore:smart-notifications';

    protected $description = 'Run smart notification checks (weight reminder, checkin reminder, streak risk)';

    public function handle(): int
    {
        $this->info('Running smart notifications...');

        $service = new SmartNotificationService;
        $clients = Client::where('status', 'activo')->pluck('id');

        foreach ($clients as $clientId) {
            $service->checkInactivity($clientId);
        }

        $this->info("Checked {$clients->count()} active clients.");

        return self::SUCCESS;
    }
}
