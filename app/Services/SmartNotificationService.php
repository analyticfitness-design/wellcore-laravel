<?php

namespace App\Services;

use App\Models\Checkin;
use App\Models\Client;
use App\Models\TrainingLog;
use App\Models\WeightLog;
use App\Models\WellcoreNotification;

class SmartNotificationService
{
    public function checkInactivity(int $clientId): void
    {
        $client = Client::find($clientId);
        if (!$client) return;

        // Check last weight log
        $lastWeight = WeightLog::where('client_id', $clientId)->latest('date')->first();
        if ($lastWeight && $lastWeight->date && $lastWeight->date->diffInDays(now()) >= 5) {
            $this->createIfNotExists($clientId, 'weight_reminder',
                'Recordatorio de peso',
                'No has registrado tu peso en ' . $lastWeight->date->diffInDays(now()) . ' dias. Registralo para mantener tu seguimiento al dia.',
                '/client/metrics'
            );
        }

        // Check last checkin
        $lastCheckin = Checkin::where('client_id', $clientId)->latest('checkin_date')->first();
        if (!$lastCheckin || ($lastCheckin->checkin_date && $lastCheckin->checkin_date->diffInDays(now()) >= 7)) {
            $this->createIfNotExists($clientId, 'checkin_reminder',
                'Check-in pendiente',
                'Tu check-in semanal esta pendiente. Tu coach necesita tus datos para ajustar tu plan.',
                '/client/checkin'
            );
        }

        // Check streak at risk (trained yesterday but not today, and it's after 6pm)
        $lastTraining = TrainingLog::where('client_id', $clientId)
            ->where('completed', true)
            ->latest('log_date')
            ->first();

        if ($lastTraining && $lastTraining->log_date && $lastTraining->log_date->isYesterday() && now()->hour >= 18) {
            $trainedToday = TrainingLog::where('client_id', $clientId)
                ->where('completed', true)
                ->where('log_date', now()->toDateString())
                ->exists();

            if (!$trainedToday) {
                $this->createIfNotExists($clientId, 'streak_risk',
                    'Tu racha esta en riesgo!',
                    'Entrenaste ayer pero hoy aun no. No pierdas tu racha — registra tu entrenamiento.',
                    '/client/training'
                );
            }
        }
    }

    private function createIfNotExists(int $clientId, string $type, string $title, string $body, string $link): void
    {
        $exists = WellcoreNotification::where('user_id', $clientId)
            ->where('user_type', 'client')
            ->where('type', $type)
            ->where('created_at', '>=', now()->startOfDay())
            ->exists();

        if (!$exists) {
            WellcoreNotification::create([
                'user_id' => $clientId,
                'user_type' => 'client',
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'link' => $link,
            ]);
        }
    }
}
