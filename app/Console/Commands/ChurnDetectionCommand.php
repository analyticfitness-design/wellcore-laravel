<?php

namespace App\Console\Commands;

use App\Enums\UserType;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\TrainingLog;
use App\Models\WellcoreNotification;
use App\Models\WorkoutSession;
use Illuminate\Console\Command;

class ChurnDetectionCommand extends Command
{
    protected $signature = 'wellcore:churn-detection';

    protected $description = 'Detect at-risk clients based on activity';

    public function handle(): int
    {
        $this->info('Running churn detection...');

        $clients = Client::where('status', 'activo')->get();
        $flagged = 0;

        foreach ($clients as $client) {
            $flagged += $this->checkTrainingInactivity($client);
            $flagged += $this->checkCheckinInactivity($client);
        }

        $this->info("Flagged {$flagged} churn risk notifications.");

        return self::SUCCESS;
    }

    private function checkTrainingInactivity(Client $client): int
    {
        $lastSession = WorkoutSession::where('client_id', $client->id)
            ->latest('session_date')
            ->value('session_date');

        $lastLog = TrainingLog::where('client_id', $client->id)
            ->where('completed', true)
            ->latest('log_date')
            ->value('log_date');

        $lastActivity = collect([$lastSession, $lastLog])->filter()->max();

        if (! $lastActivity || now()->diffInDays($lastActivity) < 14) {
            return 0;
        }

        $days = now()->diffInDays($lastActivity);

        return $this->createAdminNotification(
            $client,
            "{$client->name} no entrena hace {$days} dias",
        );
    }

    private function checkCheckinInactivity(Client $client): int
    {
        $lastCheckin = Checkin::where('client_id', $client->id)
            ->latest('checkin_date')
            ->value('checkin_date');

        if (! $lastCheckin || now()->diffInDays($lastCheckin) < 21) {
            return 0;
        }

        $days = now()->diffInDays($lastCheckin);

        return $this->createAdminNotification(
            $client,
            "{$client->name} no hace check-in hace {$days} dias",
        );
    }

    private function createAdminNotification(Client $client, string $message): int
    {
        $alreadyExists = WellcoreNotification::where('user_type', UserType::Admin)
            ->where('user_id', 1)
            ->where('type', 'churn_risk')
            ->where('body', $message)
            ->where('created_at', '>=', now()->startOfDay())
            ->exists();

        if ($alreadyExists) {
            return 0;
        }

        WellcoreNotification::create([
            'user_type' => UserType::Admin,
            'user_id' => 1,
            'type' => 'churn_risk',
            'title' => 'Riesgo de abandono',
            'body' => $message,
        ]);

        return 1;
    }
}
