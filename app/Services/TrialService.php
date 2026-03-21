<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrialService
{
    public const TRIAL_DAYS = 3;
    public const TRIAL_PLAN = 'metodo'; // Give trial users the Method experience

    public static function startTrial(int $clientId): array
    {
        $client = Client::find($clientId);
        if (!$client) return ['success' => false, 'error' => 'Cliente no encontrado'];

        // Check if client already had a trial (raw column — not in enum cast)
        $existing = DB::table('clients')->where('id', $clientId)->value('trial_started_at');
        if ($existing) {
            return ['success' => false, 'error' => 'Ya utilizaste tu periodo de prueba gratuito.'];
        }

        try {
            DB::table('clients')->where('id', $clientId)->update([
                'trial_started_at' => now(),
                'trial_ends_at'    => now()->addDays(self::TRIAL_DAYS),
                'status'           => 'trial',
                'plan_slug'        => self::TRIAL_PLAN,
            ]);

            // Award welcome coins
            WellCoinsService::earn($clientId, 'first_login', 'Inicio de trial gratuito');

            // Log
            AuditService::log('trial_started');

            return [
                'success' => true,
                'ends_at' => now()->addDays(self::TRIAL_DAYS)->format('d/m/Y H:i'),
                'plan'    => self::TRIAL_PLAN,
            ];
        } catch (\Exception $e) {
            Log::error('Trial start failed', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al iniciar trial.'];
        }
    }

    public static function isTrialActive(Client $client): bool
    {
        $endsAt = DB::table('clients')->where('id', $client->id)->value('trial_ends_at');
        if (!$endsAt) return false;
        return now()->lt(\Carbon\Carbon::parse($endsAt));
    }

    public static function getTrialDaysRemaining(Client $client): int
    {
        $endsAt = DB::table('clients')->where('id', $client->id)->value('trial_ends_at');
        if (!$endsAt) return 0;
        return max(0, (int) now()->diffInDays(\Carbon\Carbon::parse($endsAt), false));
    }

    public static function expireTrial(int $clientId): void
    {
        $row = DB::table('clients')->where('id', $clientId)->first(['status']);
        if (!$row || $row->status !== 'trial') return;

        DB::table('clients')->where('id', $clientId)->update([
            'status' => 'trial_expired',
        ]);

        AuditService::log('trial_expired');
    }
}
