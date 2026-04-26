<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Console\Command;

class CleanupPendingPaymentsCommand extends Command
{
    protected $signature = 'wellcore:cleanup-pending-payments
                            {--hours=24 : Mark payments older than this many hours as voided}
                            {--dry-run : Show what would be voided without making changes}';

    protected $description = 'Void orphan pending payments older than 24 h (Wompi never confirmed them)';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');

        $query = Payment::where('status', PaymentStatus::Pending)
            ->where('created_at', '<', now()->subHours($hours));

        $count = $query->count();

        if ($count === 0) {
            $this->info("No orphan pending payments found (>{$hours}h old).");

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("[DRY RUN] Would void {$count} pending payment(s) older than {$hours}h.");

            return self::SUCCESS;
        }

        $voided = $query->update(['status' => PaymentStatus::Voided]);

        \Log::info("CleanupPendingPayments: voided {$voided} orphan pending payments.");
        $this->info("Voided {$voided} orphan pending payment(s).");

        return self::SUCCESS;
    }
}
