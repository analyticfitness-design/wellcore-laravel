<?php

namespace App\Console\Commands;

use App\Services\TrialService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireTrialsCommand extends Command
{
    protected $signature = 'wellcore:expire-trials';
    protected $description = 'Expire trial accounts that have exceeded the trial period';

    public function handle(): void
    {
        // Query raw to avoid enum cast mismatch on status column
        $expired = DB::table('clients')
            ->where('status', 'trial')
            ->where('trial_ends_at', '<', now())
            ->get(['id', 'name', 'email']);

        foreach ($expired as $client) {
            TrialService::expireTrial($client->id);
            $this->info("Trial expired: {$client->name} ({$client->email})");
        }

        $this->info("Total expired: {$expired->count()}");
    }
}
