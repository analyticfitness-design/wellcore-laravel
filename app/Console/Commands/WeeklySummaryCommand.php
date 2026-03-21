<?php

namespace App\Console\Commands;

use App\Mail\WeeklySummary;
use App\Models\Client;
use App\Models\TrainingLog;
use App\Models\Checkin;
use App\Models\HabitLog;
use App\Models\WellcoreNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class WeeklySummaryCommand extends Command
{
    protected $signature = 'wellcore:weekly-summary';
    protected $description = 'Generate weekly summary notifications for all active clients';

    public function handle(): int
    {
        $this->info('Generating weekly summaries...');

        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $generated = 0;

        Client::where('status', 'activo')->chunk(50, function ($clients) use ($weekStart, $weekEnd, &$generated) {
            foreach ($clients as $client) {
                $trainDays = TrainingLog::where('client_id', $client->id)
                    ->where('completed', true)
                    ->whereBetween('log_date', [$weekStart, $weekEnd])
                    ->count();

                $hasCheckin = Checkin::where('client_id', $client->id)
                    ->whereBetween('checkin_date', [$weekStart, $weekEnd])
                    ->exists();

                $habitCount = HabitLog::where('client_id', $client->id)
                    ->whereBetween('log_date', [$weekStart, $weekEnd])
                    ->where('value', true)
                    ->count();

                $summaryData = [
                    'train_days'   => $trainDays,
                    'has_checkin'  => $hasCheckin,
                    'habit_count'  => $habitCount,
                    'week_start'   => $weekStart->toDateString(),
                    'week_end'     => $weekEnd->toDateString(),
                ];

                WellcoreNotification::create([
                    'user_type' => 'client',
                    'user_id' => $client->id,
                    'type' => 'weekly_summary',
                    'title' => 'Resumen de tu semana',
                    'body' => "Entrenaste {$trainDays} dias, " .
                              ($hasCheckin ? 'completaste tu check-in' : 'no hiciste check-in') .
                              " y registraste {$habitCount} habitos. " .
                              ($trainDays >= 4 ? 'Excelente semana!' : 'La proxima semana sera mejor!'),
                ]);

                if ($client->email) {
                    Mail::to($client->email)->queue(new WeeklySummary($client, $summaryData));
                }

                $generated++;
            }
        });

        $this->info("Generated {$generated} weekly summaries.");
        return self::SUCCESS;
    }
}
