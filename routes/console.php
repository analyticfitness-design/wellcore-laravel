<?php

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
Schedule::command('wellcore:match-gifs-from-json --reset')->weeklyOn(1, '03:00'); // Monday 3am — remap GIFs for all new plans
