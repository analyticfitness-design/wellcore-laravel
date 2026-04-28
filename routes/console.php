<?php

use App\Jobs\ExpireCoachInvitationsJob;
use App\Models\AuthToken;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Commands (Cron Replacements)
|--------------------------------------------------------------------------
|
| These replace the legacy cron/*.php scripts.
| Run the scheduler: php artisan schedule:run
| Production: * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
|
*/

Schedule::command('wellcore:auto-renewal')->dailyAt('07:00');
Schedule::command('wellcore:behavioral-triggers')->dailyAt('08:00');
Schedule::command('wellcore:weekly-summary')->weeklyOn(0, '20:00'); // Sunday 8pm
Schedule::command('wellcore:expire-trials')->hourly(); // Check trial expirations every hour
Schedule::command('wellcore:smart-notifications')->dailyAt('09:00');
Schedule::command('wellcore:churn-detection')->dailyAt('10:00');
Schedule::command('wellcore:match-gifs-from-json --reset')->weeklyOn(1, '03:00'); // Monday 3am — remap GIFs for all new plans

// Expire overdue coach invitations (status: Sent/Pending with passed expiry_date)
Schedule::job(new ExpireCoachInvitationsJob)->dailyAt('00:05');

// P2.2: Void orphan pending payments older than 24 h (Wompi never confirmed them)
Schedule::command('wellcore:cleanup-pending-payments')->dailyAt('02:00');

// Expire manual payment proofs that were not reviewed within 7 days
Schedule::command('wellcore:expire-payment-proofs')->daily()->runInBackground();

// Strategy Hub — archive completed drops older than 30 days
Schedule::command('wellcore:archive-old-drops')->dailyAt('03:00')->withoutOverlapping(60);

// Prune expired auth tokens and inactive sessions older than 14 days
Schedule::call(function () {
    AuthToken::where('expires_at', '<', now())
        ->orWhere(function ($q) {
            $q->whereNotNull('last_used_at')
                ->where('last_used_at', '<', now()->subDays(14));
        })
        ->delete();
})->daily()->name('auth-tokens:prune');

// Coach impersonation cleanup — closes orphaned logs with expired tokens
Schedule::command('wellcore:close-orphaned-impersonations')
    ->dailyAt('03:30')
    ->name('impersonations:close-orphaned');
