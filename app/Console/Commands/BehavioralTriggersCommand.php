<?php

namespace App\Console\Commands;

use App\Mail\CheckinReminder;
use App\Models\AutoMessageLog;
use App\Models\Client;
use App\Models\Checkin;
use App\Models\TrainingLog;
use App\Models\WellcoreNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class BehavioralTriggersCommand extends Command
{
    protected $signature = 'wellcore:behavioral-triggers';
    protected $description = 'Send behavioral trigger notifications to clients based on activity patterns';

    public function handle(): int
    {
        $this->info('Running behavioral triggers...');

        $clients = Client::where('status', 'activo')->get();
        $triggered = 0;

        foreach ($clients as $client) {
            // Check for inactivity (no training in 3+ days)
            $lastTraining = TrainingLog::where('client_id', $client->id)
                ->where('completed', true)
                ->latest('log_date')
                ->first();

            if ($lastTraining && $lastTraining->log_date->diffInDays(now()) >= 3) {
                $alreadySent = AutoMessageLog::where('client_id', $client->id)
                    ->where('trigger_type', 'inactivity_3d')
                    ->where('date_sent', today())
                    ->exists();

                if (!$alreadySent) {
                    WellcoreNotification::create([
                        'user_type' => 'client',
                        'user_id' => $client->id,
                        'type' => 'behavioral_trigger',
                        'title' => 'Te extrañamos en el gym',
                        'body' => 'Llevas ' . $lastTraining->log_date->diffInDays(now()) . ' dias sin entrenar. Cada dia cuenta.',
                    ]);

                    AutoMessageLog::create([
                        'client_id' => $client->id,
                        'trigger_type' => 'inactivity_3d',
                        'channel' => 'notification',
                        'date_sent' => today(),
                    ]);
                    $triggered++;
                }
            }

            // Check for missed check-in (no checkin this week)
            $hasCheckin = Checkin::where('client_id', $client->id)
                ->where('checkin_date', '>=', now()->startOfWeek())
                ->exists();

            if (!$hasCheckin && now()->dayOfWeek >= 5) { // Friday+
                $alreadySent = AutoMessageLog::where('client_id', $client->id)
                    ->where('trigger_type', 'missed_checkin')
                    ->where('date_sent', today())
                    ->exists();

                if (!$alreadySent) {
                    WellcoreNotification::create([
                        'user_type' => 'client',
                        'user_id' => $client->id,
                        'type' => 'behavioral_trigger',
                        'title' => 'No olvides tu check-in semanal',
                        'body' => 'Tu coach necesita saber como va tu semana. Completa tu check-in.',
                    ]);

                    if ($client->email) {
                        Mail::to($client->email)->queue(new CheckinReminder($client));
                    }

                    AutoMessageLog::create([
                        'client_id' => $client->id,
                        'trigger_type' => 'missed_checkin',
                        'channel' => 'notification',
                        'date_sent' => today(),
                    ]);
                    $triggered++;
                }
            }
        }

        $this->info("Triggered {$triggered} notifications.");
        return self::SUCCESS;
    }
}
