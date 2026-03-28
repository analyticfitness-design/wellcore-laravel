<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin', ['title' => 'Campaign Tracker'])]
class CampaignTracker extends Component
{
    // Filters
    public string $dateRange = '30';

    // Pixel status
    public array $pixelStatus = [
        'is_active' => false,
        'pixel_id' => null,
        'capi_configured' => false,
        'test_mode' => false,
    ];

    // Funnel metrics
    public array $funnelData = [
        'total_visits' => 0,
        'utm_visits' => 0,
        'inscriptions' => 0,
        'payments' => 0,
        'conversion_rate' => 0,
        'utm_percentage' => 0,
        'inscription_rate' => 0,
        'revenue' => 0,
    ];

    // Breakdowns
    public array $campaignBreakdown = [];
    public array $sourceBreakdown = [];
    public array $topLandingPages = [];
    public array $recentConversions = [];
    public array $deviceBreakdown = [];

    public function mount(): void
    {
        $this->loadAllData();
    }

    public function updatedDateRange(): void
    {
        $this->loadAllData();
    }

    protected function loadAllData(): void
    {
        $this->loadPixelStatus();
        $this->loadFunnelData();
        $this->loadCampaignBreakdown();
        $this->loadSourceBreakdown();
        $this->loadTopLandingPages();
        $this->loadRecentConversions();
        $this->loadDeviceBreakdown();
    }

    protected function loadPixelStatus(): void
    {
        $pixelId = config('app.meta_pixel_id');
        $accessToken = config('services.meta.access_token');
        $testCode = config('services.meta.test_event_code');

        $this->pixelStatus = [
            'is_active' => filled($pixelId),
            'pixel_id' => $pixelId,
            'capi_configured' => filled($accessToken),
            'test_mode' => filled($testCode),
        ];
    }

    protected function loadFunnelData(): void
    {
        try {
            $days = (int) $this->dateRange;
            $cacheKey = "campaign_funnel_{$days}";

            $this->funnelData = Cache::remember($cacheKey, 300, function () use ($days) {
                $baseQuery = DB::table('page_visits')
                    ->where('created_at', '>=', now()->subDays($days));

                $totalVisits = (clone $baseQuery)->count();
                $utmVisits = (clone $baseQuery)->whereNotNull('utm_source')->count();
                $inscriptions = (clone $baseQuery)->whereNotNull('converted_at')->where('conversion_type', 'inscription')->count();
                $payments = (clone $baseQuery)->whereNotNull('converted_at')->where('conversion_type', 'payment')->count();

                $revenue = DB::table('page_visits')
                    ->join('payments', 'page_visits.payment_id', '=', 'payments.id')
                    ->where('page_visits.created_at', '>=', now()->subDays($days))
                    ->where('payments.status', 'approved')
                    ->sum('payments.amount');

                $conversionRate = $utmVisits > 0
                    ? round((($inscriptions + $payments) / $utmVisits) * 100, 1)
                    : 0;

                $utmPercentage = $totalVisits > 0
                    ? round(($utmVisits / $totalVisits) * 100, 1)
                    : 0;

                $inscriptionRate = $utmVisits > 0
                    ? round(($inscriptions / $utmVisits) * 100, 1)
                    : 0;

                return [
                    'total_visits' => $totalVisits,
                    'utm_visits' => $utmVisits,
                    'inscriptions' => $inscriptions,
                    'payments' => $payments,
                    'conversion_rate' => $conversionRate,
                    'utm_percentage' => $utmPercentage,
                    'inscription_rate' => $inscriptionRate,
                    'revenue' => $revenue,
                ];
            });
        } catch (\Exception $e) {
            Log::warning('CampaignTracker: Failed to load funnel data', ['error' => $e->getMessage()]);
        }
    }

    protected function loadCampaignBreakdown(): void
    {
        try {
            $days = (int) $this->dateRange;
            $cacheKey = "campaign_breakdown_{$days}";

            $this->campaignBreakdown = Cache::remember($cacheKey, 300, function () use ($days) {
                return DB::table('page_visits')
                    ->leftJoin('payments', 'page_visits.payment_id', '=', 'payments.id')
                    ->whereNotNull('page_visits.utm_campaign')
                    ->where('page_visits.utm_campaign', '!=', '')
                    ->where('page_visits.created_at', '>=', now()->subDays($days))
                    ->selectRaw("
                        page_visits.utm_campaign as name,
                        COUNT(DISTINCT page_visits.id) as visits,
                        SUM(CASE WHEN page_visits.conversion_type = 'inscription' AND page_visits.converted_at IS NOT NULL THEN 1 ELSE 0 END) as inscriptions,
                        SUM(CASE WHEN page_visits.conversion_type = 'payment' AND page_visits.converted_at IS NOT NULL THEN 1 ELSE 0 END) as payments,
                        COALESCE(SUM(CASE WHEN payments.status = 'approved' THEN payments.amount ELSE 0 END), 0) as revenue
                    ")
                    ->groupBy('page_visits.utm_campaign')
                    ->orderByDesc('visits')
                    ->limit(20)
                    ->get()
                    ->map(function ($row) {
                        $row->conversion_rate = $row->visits > 0
                            ? round((($row->inscriptions + $row->payments) / $row->visits) * 100, 1)
                            : 0;
                        return (array) $row;
                    })
                    ->toArray();
            });
        } catch (\Exception $e) {
            Log::warning('CampaignTracker: Failed to load campaign breakdown', ['error' => $e->getMessage()]);
            $this->campaignBreakdown = [];
        }
    }

    protected function loadSourceBreakdown(): void
    {
        try {
            $days = (int) $this->dateRange;
            $cacheKey = "campaign_sources_{$days}";

            $this->sourceBreakdown = Cache::remember($cacheKey, 300, function () use ($days) {
                return DB::table('page_visits')
                    ->whereNotNull('utm_source')
                    ->where('utm_source', '!=', '')
                    ->where('created_at', '>=', now()->subDays($days))
                    ->selectRaw("
                        utm_source as name,
                        COUNT(*) as visits,
                        SUM(CASE WHEN converted_at IS NOT NULL THEN 1 ELSE 0 END) as conversions
                    ")
                    ->groupBy('utm_source')
                    ->orderByDesc('visits')
                    ->limit(10)
                    ->get()
                    ->map(fn ($row) => (array) $row)
                    ->toArray();
            });
        } catch (\Exception $e) {
            Log::warning('CampaignTracker: Failed to load source breakdown', ['error' => $e->getMessage()]);
            $this->sourceBreakdown = [];
        }
    }

    protected function loadTopLandingPages(): void
    {
        try {
            $days = (int) $this->dateRange;
            $cacheKey = "campaign_landing_pages_{$days}";

            $this->topLandingPages = Cache::remember($cacheKey, 300, function () use ($days) {
                return DB::table('page_visits')
                    ->whereNotNull('landing_page')
                    ->where('landing_page', '!=', '')
                    ->where('created_at', '>=', now()->subDays($days))
                    ->selectRaw("
                        landing_page as url,
                        COUNT(*) as visits,
                        SUM(CASE WHEN converted_at IS NOT NULL THEN 1 ELSE 0 END) as conversions
                    ")
                    ->groupBy('landing_page')
                    ->orderByDesc('visits')
                    ->limit(10)
                    ->get()
                    ->map(fn ($row) => (array) $row)
                    ->toArray();
            });
        } catch (\Exception $e) {
            Log::warning('CampaignTracker: Failed to load landing pages', ['error' => $e->getMessage()]);
            $this->topLandingPages = [];
        }
    }

    protected function loadRecentConversions(): void
    {
        try {
            $this->recentConversions = Cache::remember('campaign_recent_conversions', 180, function () {
                return DB::table('page_visits')
                    ->whereNotNull('converted_at')
                    ->orderByDesc('converted_at')
                    ->limit(10)
                    ->select([
                        'conversion_type',
                        'utm_campaign',
                        'utm_source',
                        'utm_medium',
                        'device_type',
                        'landing_page',
                        'converted_at',
                        'created_at',
                    ])
                    ->get()
                    ->map(function ($row) {
                        $row->time_ago = \Carbon\Carbon::parse($row->converted_at)->diffForHumans();
                        return (array) $row;
                    })
                    ->toArray();
            });
        } catch (\Exception $e) {
            Log::warning('CampaignTracker: Failed to load recent conversions', ['error' => $e->getMessage()]);
            $this->recentConversions = [];
        }
    }

    protected function loadDeviceBreakdown(): void
    {
        try {
            $days = (int) $this->dateRange;
            $cacheKey = "campaign_devices_{$days}";

            $this->deviceBreakdown = Cache::remember($cacheKey, 300, function () use ($days) {
                return DB::table('page_visits')
                    ->where('created_at', '>=', now()->subDays($days))
                    ->selectRaw("
                        COALESCE(device_type, 'unknown') as device,
                        COUNT(*) as count
                    ")
                    ->groupBy('device_type')
                    ->orderByDesc('count')
                    ->get()
                    ->map(fn ($row) => (array) $row)
                    ->toArray();
            });
        } catch (\Exception $e) {
            Log::warning('CampaignTracker: Failed to load device breakdown', ['error' => $e->getMessage()]);
            $this->deviceBreakdown = [];
        }
    }

    public function render()
    {
        return view('livewire.admin.campaign-tracker');
    }
}
