<?php

namespace App\Console\Commands;

use App\Models\AuthToken;
use App\Models\ImpersonationLog;
use Illuminate\Console\Command;

class CloseOrphanedImpersonations extends Command
{
    protected $signature = 'wellcore:close-orphaned-impersonations';
    protected $description = 'Closes impersonation_logs whose token has expired but ended_at is still null.';

    public function handle(): int
    {
        $closed = 0;
        ImpersonationLog::whereNull('ended_at')
            ->whereNotNull('token')
            ->chunkById(200, function ($logs) use (&$closed) {
                foreach ($logs as $log) {
                    $tokenRow = AuthToken::where('token', $log->token)->first();
                    $expired  = ! $tokenRow || $tokenRow->expires_at < now();
                    if ($expired) {
                        $log->update(['ended_at' => $tokenRow?->expires_at ?? now()]);
                        if ($tokenRow) $tokenRow->delete();
                        $closed++;
                    }
                }
            });
        $this->info("Closed {$closed} orphaned impersonation logs.");
        return self::SUCCESS;
    }
}
