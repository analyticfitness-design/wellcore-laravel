<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TerraService
{
    private string $apiKey;
    private string $devId;
    private string $baseUrl = 'https://api.tryterra.co/v2';

    public function __construct()
    {
        $this->apiKey = config('services.terra.api_key', '');
        $this->devId = config('services.terra.dev_id', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->devId);
    }

    public function generateWidgetSession(int $clientId): ?array
    {
        if (!$this->isConfigured()) return null;

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'dev-id' => $this->devId,
            ])->post($this->baseUrl . '/auth/generateWidgetSession', [
                'reference_id' => 'wc_client_' . $clientId,
                'providers' => 'APPLE,GARMIN,FITBIT,WHOOP,OURA,SAMSUNG',
                'language' => 'es',
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Terra widget session failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function getDailyData(string $terraUserId, string $date): ?array
    {
        return $this->getData('/daily', $terraUserId, $date, $date);
    }

    public function getSleepData(string $terraUserId, string $date): ?array
    {
        return $this->getData('/sleep', $terraUserId, $date, $date);
    }

    public function getActivityData(string $terraUserId, string $date): ?array
    {
        return $this->getData('/activity', $terraUserId, $date, $date);
    }

    public function getBodyData(string $terraUserId, string $date): ?array
    {
        return $this->getData('/body', $terraUserId, $date, $date);
    }

    private function getData(string $endpoint, string $userId, string $startDate, string $endDate): ?array
    {
        if (!$this->isConfigured()) return null;

        $cacheKey = "terra:{$userId}:{$endpoint}:{$startDate}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($endpoint, $userId, $startDate, $endDate) {
            try {
                $response = Http::withHeaders([
                    'x-api-key' => $this->apiKey,
                    'dev-id' => $this->devId,
                ])->get($this->baseUrl . $endpoint, [
                    'user_id' => $userId,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);

                return $response->successful() ? $response->json() : null;
            } catch (\Exception $e) {
                Log::error("Terra {$endpoint} failed", ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    public function calculateRecoveryScore(array $sleepData, array $bodyData): int
    {
        $sleepScore = 0;
        $hrvScore = 0;

        // Sleep quality (0-40 points)
        $sleepHours = ($sleepData['sleep_durations_data']['other']['duration_total_sleep_seconds'] ?? 0) / 3600;
        if ($sleepHours >= 7 && $sleepHours <= 9) $sleepScore = 40;
        elseif ($sleepHours >= 6) $sleepScore = 25;
        else $sleepScore = 10;

        // HRV (0-30 points)
        $hrv = $bodyData['heart_rate_data']['summary']['avg_hrv'] ?? 0;
        if ($hrv > 50) $hrvScore = 30;
        elseif ($hrv > 35) $hrvScore = 20;
        else $hrvScore = 10;

        // Resting HR (0-30 points)
        $restingHR = $bodyData['heart_rate_data']['summary']['resting_hr_bpm'] ?? 70;
        $hrScore = $restingHR < 60 ? 30 : ($restingHR < 70 ? 20 : 10);

        return min(100, $sleepScore + $hrvScore + $hrScore);
    }
}
